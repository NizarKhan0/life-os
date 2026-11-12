<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AutomationLog;
use App\Models\Habit;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class BriefingService
{
    /**
     * Gather structured morning briefing data for a user.
     *
     * @return array{
     *     date_formatted: string,
     *     meetings: Collection<int, Meeting>,
     *     top_tasks: Collection<int, Task>,
     *     unlogged_habits: Collection<int, Habit>,
     *     yesterday_expense: float,
     *     total_wallet_balance: float,
     *     wallets: Collection<int, Wallet>
     * }
     */
    public function summaryData(User $user): array
    {
        $now = Carbon::now('Asia/Kuala_Lumpur');
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $yesterdayDate = $now->copy()->subDay()->toDateString();

        $meetings = Meeting::query()
            ->where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->orderBy('start_time')
            ->get();

        $topTasks = Task::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->whereIn('priority', ['urgent', 'high'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 ELSE 3 END")
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(3)
            ->get();

        if ($topTasks->isEmpty()) {
            $topTasks = Task::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'completed')
                ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('due_date')
                ->limit(3)
                ->get();
        }

        $activeHabits = Habit::query()
            ->with('logs')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        /** @var Collection<int, Habit> $unloggedHabits */
        $unloggedHabits = $activeHabits
            ->reject(fn (Habit $habit): bool => $habit->isCompletedToday())
            ->values();

        $yesterdayExpense = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereDate('transaction_date', $yesterdayDate)
            ->sum('amount');

        $wallets = Wallet::query()
            ->with(['transactions', 'incomingTransfers'])
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $totalWalletBalance = round(
            (float) $wallets->sum(fn (Wallet $wallet): float => $wallet->currentBalance()),
            2
        );

        return [
            'date_formatted' => $now->translatedFormat('l, d M Y'),
            'meetings' => $meetings,
            'top_tasks' => $topTasks,
            'unlogged_habits' => $unloggedHabits,
            'yesterday_expense' => round($yesterdayExpense, 2),
            'total_wallet_balance' => $totalWalletBalance,
            'wallets' => $wallets,
        ];
    }

    /**
     * Generate formatted HTML Telegram Morning Briefing report for a user.
     */
    public function generateForUser(User $user): string
    {
        $data = $this->summaryData($user);

        $lines = [
            '☀️ <b>LifeOS Morning Briefing</b>',
            '🗓 <b>'.e($data['date_formatted']).'</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            '',
            "📅 <b>Jadual Meeting Hari Ini ({$data['meetings']->count()}):</b>",
        ];

        if ($data['meetings']->isEmpty()) {
            $lines[] = '  • Tiada mesyuarat dijadualkan hari ini.';
        } else {
            foreach ($data['meetings'] as $meeting) {
                $timeStr = $meeting->start_time->format('h:i A');
                $locStr = ($meeting->location_or_link !== null && $meeting->location_or_link !== '')
                    ? ' — <i>'.e($meeting->location_or_link).'</i>'
                    : '';
                $lines[] = "  • <code>{$timeStr}</code> <b>".e($meeting->title)."</b>{$locStr}";
            }
        }

        $lines[] = '';
        $lines[] = "🔥 <b>Top 3 Tugasan Utama ({$data['top_tasks']->count()}):</b>";

        if ($data['top_tasks']->isEmpty()) {
            $lines[] = '  • Semua tugasan penting telah selesai!';
        } else {
            foreach ($data['top_tasks'] as $task) {
                $priorityIcon = match ($task->priority) {
                    'urgent' => '🔴 [URGENT]',
                    'high' => '🟠 [HIGH]',
                    'medium' => '🔵 [MEDIUM]',
                    default => '⚪ [LOW]',
                };
                $dueBadge = $task->due_date !== null ? ' <i>(Due: '.$task->due_date->format('d M').')</i>' : '';
                $lines[] = "  • {$priorityIcon} ".e($task->title).$dueBadge;
            }
        }

        $lines[] = '';
        $lines[] = "⚡ <b>Tabiat Belum Dilog Hari Ini ({$data['unlogged_habits']->count()}):</b>";

        if ($data['unlogged_habits']->isEmpty()) {
            $lines[] = '  • Syabas! Semua tabiat hari ini telah dilog.';
        } else {
            foreach ($data['unlogged_habits'] as $habit) {
                $streak = $habit->currentStreak();
                $lines[] = '  • '.e($habit->name)." (Streak: 🔥 {$streak} hari)";
            }
        }

        $lines[] = '';
        $lines[] = '💰 <b>Ringkasan Poket & Dompet:</b>';
        $lines[] = '  • Belanja Semalam: <b>RM '.number_format($data['yesterday_expense'], 2).'</b>';
        $lines[] = '  • Jumlah Baki Dompet: <b>RM '.number_format($data['total_wallet_balance'], 2).'</b>';

        foreach ($data['wallets'] as $wallet) {
            $lines[] = '    ◦ '.e($wallet->name).': RM '.number_format($wallet->currentBalance(), 2);
        }

        $lines[] = '';
        $lines[] = '🚀 <i>Semoga hari anda produktif dan diberkati!</i>';

        return implode("\n", $lines);
    }

    /**
     * Generate, send via TelegramService, and record an AutomationLog entry.
     */
    public function dispatchBriefingForUser(
        User $user,
        TelegramService $telegramService,
        string $triggeredBy = 'scheduler'
    ): AutomationLog {
        $message = $this->generateForUser($user);
        $configured = $telegramService->isConfigured();
        $sent = $telegramService->sendMorningBriefing($message);

        $status = match (true) {
            $sent => 'sent',
            ! $configured => 'simulated',
            default => 'failed',
        };

        return AutomationLog::query()->create([
            'user_id' => $user->id,
            'type' => 'morning_briefing',
            'channel' => 'telegram',
            'status' => $status,
            'triggered_by' => $triggeredBy,
            'message_preview' => $message,
            'sent_at' => Carbon::now(),
        ]);
    }
}

