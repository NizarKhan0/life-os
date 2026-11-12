<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Habit;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Subscription;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramService
{
    private string $botToken;

    private string $defaultChatId;

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token', '');
        $this->defaultChatId = (string) config('services.telegram.default_chat_id', '');
    }

    /**
     * Check whether Telegram credentials are configured.
     */
    public function isConfigured(?string $chatId = null): bool
    {
        $targetChatId = $chatId ?? $this->defaultChatId;

        return $this->botToken !== '' && $targetChatId !== '';
    }

    /**
     * Send a message to a Telegram chat via the Bot API.
     */
    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'HTML'): bool
    {
        $targetChatId = $chatId ?? $this->defaultChatId;

        if (! $this->isConfigured($targetChatId)) {
            Log::info('TelegramService: Credentials not configured. Message logged locally.', [
                'message_preview' => mb_substr($message, 0, 160),
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'chat_id' => $targetChatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
                ]
            );

            if (! $response->successful()) {
                Log::warning('TelegramService: Failed to send Telegram message.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('TelegramService: Exception while sending Telegram message.', [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format and send a reminder for an active Goal.
     */
    public function sendGoalReminder(Goal $goal, ?string $chatId = null): bool
    {
        $progress = number_format($goal->progressPercentage(), 1);
        $daysRemaining = $goal->daysRemaining();

        $daysText = match (true) {
            $daysRemaining === null => 'Tiada tarikh sasaran',
            $daysRemaining < 0 => 'Lewat '.abs($daysRemaining).' hari',
            $daysRemaining === 0 => 'Hari terakhir (Hari ini!)',
            default => "{$daysRemaining} hari lagi",
        };

        $pendingMilestones = $goal->milestones
            ->where('is_completed', false)
            ->take(3)
            ->map(fn (Milestone $m): string => '  • '.e($m->title))
            ->implode("\n");

        $lines = [
            '🎯 <b>Peringatan Matlamat (LifeOS)</b>',
            '',
            '📌 <b>'.e($goal->title).'</b>',
            '🏷 Kategori: <code>'.e(ucfirst($goal->category)).'</code>',
            "📊 Kemajuan: <b>{$progress}%</b>",
            "⏳ Baki Masa: <b>{$daysText}</b>",
        ];

        if ($goal->target_value !== null && (float) $goal->target_value > 0) {
            $unit = $goal->unit ? ' '.e($goal->unit) : '';
            $currentFormatted = number_format((float) $goal->current_value, 2);
            $targetFormatted = number_format((float) $goal->target_value, 2);
            $lines[] = "💰 Sasaran: <b>{$currentFormatted} / {$targetFormatted}{$unit}</b>";
        }

        if ($pendingMilestones !== '') {
            $lines[] = '';
            $lines[] = '📋 <b>Milestone Belum Selesai:</b>';
            $lines[] = $pendingMilestones;
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Format and send a reminder for a scheduled Meeting.
     */
    public function sendMeetingReminder(Meeting $meeting, ?int $remindBeforeMinutes = null, ?string $chatId = null): bool
    {
        $startFormatted = $meeting->start_time->format('d M Y, h:i A');
        $endFormatted = $meeting->end_time ? ' - '.$meeting->end_time->format('h:i A') : '';

        $lines = [
            '🔔 <b>Peringatan Mesyuarat (LifeOS)</b>',
            '',
            '📅 <b>'.e($meeting->title).'</b>',
            "🕒 Masa: <b>{$startFormatted}{$endFormatted}</b>",
        ];

        if ($remindBeforeMinutes !== null) {
            $lines[] = "⏰ Peringatan: <b>{$remindBeforeMinutes} minit sebelum bermula</b>";
        }

        if ($meeting->location_or_link !== null && $meeting->location_or_link !== '') {
            $lines[] = '📍 Lokasi / Pautan: '.e($meeting->location_or_link);
        }

        if ($meeting->description !== null && $meeting->description !== '') {
            $lines[] = '';
            $lines[] = '📝 '.e($meeting->description);
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Format and send a monthly Budget threshold alert to Telegram.
     */
    public function sendBudgetAlert(Budget $budget, ?string $chatId = null): bool
    {
        $spent = $budget->spentAmount();
        $limit = (float) $budget->amount;
        $progress = $budget->progressPercentage();
        $categoryName = $budget->category->name;

        $statusIcon = $progress >= 100.0 ? '🚨' : '⚠️';
        $statusText = $progress >= 100.0 ? 'Melebihi Bajet!' : 'Hampir Had Bajet';

        $lines = [
            "{$statusIcon} <b>Peringatan Bajet Bulanan (LifeOS)</b>",
            '',
            '🗂 Kategori: <b>'.e($categoryName).'</b>',
            '📅 Bulan: <code>'.e($budget->month_year).'</code>',
            '💸 Belanja: <b>RM '.number_format($spent, 2).' / RM '.number_format($limit, 2).'</b>',
            "📊 Penggunaan: <b>{$progress}% ({$statusText})</b>",
        ];

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Format and send a reminder for pending Tasks and uncompleted Habits.
     *
     * @param  Collection<int, Task>  $tasks
     * @param  Collection<int, Habit>  $habits
     */
    public function sendTaskHabitReminder(Collection $tasks, Collection $habits, ?string $chatId = null): bool
    {
        $lines = [
            '✅ <b>Peringatan Tasks & Habits (LifeOS)</b>',
        ];

        if ($tasks->isNotEmpty()) {
            $lines[] = '';
            $lines[] = "📌 <b>Tugasan Belum Selesai ({$tasks->count()}):</b>";

            foreach ($tasks->take(5) as $task) {
                $priorityIcon = match ($task->priority) {
                    'urgent' => '🔴',
                    'high' => '🟠',
                    'medium' => '🔵',
                    default => '⚪',
                };
                $due = $task->due_date ? ' ('.$task->due_date->format('d/m').')' : '';
                $lines[] = "  {$priorityIcon} ".e($task->title).$due;
            }
        }

        if ($habits->isNotEmpty()) {
            $lines[] = '';
            $lines[] = "🔥 <b>Habit Hari Ini Belum Ditanda ({$habits->count()}):</b>";

            foreach ($habits as $habit) {
                $streak = $habit->currentStreak();
                $lines[] = '  • '.e($habit->name)." (🔥 {$streak} hari)";
            }
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Format and send a due/upcoming Subscription commitment alert to Telegram.
     */
    public function sendSubscriptionDueAlert(Subscription $subscription, ?string $chatId = null): bool
    {
        $days = $subscription->daysUntilDue();

        $dueBadge = match (true) {
            $days < 0 => 'Lewat '.abs($days).' hari!',
            $days === 0 => 'Hari Ini!',
            $days === 1 => 'Esok (1 hari lagi)',
            default => "{$days} hari lagi",
        };

        $cycleLabel = $subscription->billing_cycle === 'yearly' ? 'Tahunan' : 'Bulanan';
        $walletName = $subscription->wallet !== null ? $subscription->wallet->name : 'Tiada dompet ditetapkan';
        $autoRenewText = $subscription->auto_renew ? 'Ya (Auto-Debit)' : 'Tidak (Manual)';

        $lines = [
            '💳 <b>Peringatan Komitmen & Langganan (LifeOS)</b>',
            '',
            '📌 <b>'.e($subscription->name).'</b>',
            '🏷 Kategori: <code>'.e(ucfirst($subscription->category)).'</code>',
            '💰 Jumlah: <b>RM '.number_format((float) $subscription->amount, 2)." ({$cycleLabel})</b>",
            '📅 Tarikh Bil: <b>'.$subscription->next_billing_date->format('d M Y')." ({$dueBadge})</b>",
            '🏦 Dompet: '.e($walletName),
            "🔄 Auto-Renew: {$autoRenewText}",
        ];

        if ($subscription->notes !== null && $subscription->notes !== '') {
            $lines[] = '';
            $lines[] = '📝 '.e($subscription->notes);
        }

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Send a weekly review reminder with automatic metrics snapshot.
     *
     * @param  array<string, int|float>  $snapshot
     */
    public function sendWeeklyReviewReminder(array $snapshot, int $unprocessedDumps = 0, ?string $chatId = null): bool
    {
        $lines = [
            '🧠 <b>Masa Untuk Weekly Review (LifeOS)</b>',
            '',
            '📊 <b>Snapshot Minggu Ini:</b>',
            '  ✅ Task siap: <b>'.($snapshot['tasks_completed'] ?? 0).'</b>',
            '  ⏳ Task tertunggak: <b>'.($snapshot['tasks_pending'] ?? 0).'</b>',
            '  🔥 Check-in habit: <b>'.($snapshot['habit_checkins'] ?? 0).'</b>',
            '  📅 Mesyuarat: <b>'.($snapshot['meetings_held'] ?? 0).'</b>',
            '  💸 Belanja: <b>RM '.number_format((float) ($snapshot['total_expense'] ?? 0), 2).'</b>',
            '  💰 Pendapatan: <b>RM '.number_format((float) ($snapshot['total_income'] ?? 0), 2).'</b>',
        ];

        if ($unprocessedDumps > 0) {
            $lines[] = '';
            $lines[] = "🗒 Anda ada <b>{$unprocessedDumps} nota Brain Dump</b> yang belum diproses.";
        }

        $lines[] = '';
        $lines[] = '✍️ Luangkan 10 minit untuk jawab 3 soalan refleksi: <i>Kemenangan, Cabaran, Penambahbaikan</i>.';

        return $this->sendMessage(implode("\n", $lines), $chatId);
    }

    /**
     * Send a pre-formatted Morning Briefing report to Telegram.
     */
    public function sendMorningBriefing(string $briefingHtml, ?string $chatId = null): bool
    {
        return $this->sendMessage($briefingHtml, $chatId, 'HTML');
    }
}
