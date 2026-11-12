<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Meeting;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BriefingService;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Executive Dashboard')]
class Dashboard extends Component
{
    /**
     * Send a quick manual test briefing to Telegram.
     */
    public function sendQuickBriefing(BriefingService $briefingService, TelegramService $telegramService): void
    {
        /** @var User $user */
        $user = Auth::user();

        $log = $briefingService->dispatchBriefingForUser($user, $telegramService, 'manual');

        if ($log->status === 'sent') {
            Flux::toast(
                text: 'Morning Briefing report sent directly to your Telegram!',
                heading: 'Telegram Briefing',
                variant: 'success',
            );

            return;
        }

        if ($log->status === 'simulated') {
            Flux::toast(
                text: 'Briefing simulation recorded (Check Telegram token in .env for live messages).',
                heading: 'Simulation Recorded',
                variant: 'warning',
            );

            return;
        }

        Flux::toast(
            text: 'Failed to send to Telegram API. Please check bot connection.',
            heading: 'Telegram Error',
            variant: 'danger',
        );
    }

    /**
     * Quick toggle a task's completion status.
     */
    public function toggleTask(int $taskId): void
    {
        $task = Task::query()
            ->where('user_id', Auth::id())
            ->whereKey($taskId)
            ->first();

        if ($task === null) {
            return;
        }

        if ($task->status === 'completed') {
            $task->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);

            Flux::toast(
                text: 'Task reset to Incomplete.',
                heading: 'Task Status',
                variant: 'info',
            );
        } else {
            $task->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            Flux::toast(
                text: 'Great job! Task marked as completed.',
                heading: 'Task Completed',
                variant: 'success',
            );
        }

        unset($this->todayTasks);
    }

    /**
     * Quick 1-click check-in for a habit today.
     */
    public function checkInHabit(int $habitId): void
    {
        $habit = Habit::query()
            ->where('user_id', Auth::id())
            ->whereKey($habitId)
            ->first();

        if ($habit === null) {
            return;
        }

        $today = Carbon::today();

        $log = HabitLog::query()->firstOrCreate(
            [
                'habit_id' => $habit->id,
                'logged_date' => $today->toDateString(),
            ],
            ['count' => 0]
        );

        $log->increment('count');

        unset($this->habits);

        Flux::toast(
            text: "Habit '{$habit->name}' checked in for today! 🔥",
            heading: 'Check-in Recorded',
            variant: 'success',
        );
    }

    /**
     * Today's scheduled meetings.
     *
     * @return Collection<int, Meeting>
     */
    #[Computed]
    public function todayMeetings(): Collection
    {
        $todayStart = Carbon::today('Asia/Kuala_Lumpur')->startOfDay();
        $todayEnd = Carbon::today('Asia/Kuala_Lumpur')->endOfDay();

        return Meeting::query()
            ->where('user_id', Auth::id())
            ->where('status', 'scheduled')
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Pending urgent/high priority tasks for today.
     *
     * @return Collection<int, Task>
     */
    #[Computed]
    public function todayTasks(): Collection
    {
        return Task::query()
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    /**
     * Active habits with today's completion status.
     *
     * @return Collection<int, Habit>
     */
    #[Computed]
    public function habits(): Collection
    {
        return Habit::query()
            ->with('logs')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Financial summary: total wallet balance, this month's expense vs budget.
     *
     * @return array{
     *     total_balance: float,
     *     month_expense: float,
     *     total_budget: float,
     *     budget_percentage: float,
     *     wallets: Collection<int, Wallet>
     * }
     */
    #[Computed]
    public function financialSnapshot(): array
    {
        $userId = Auth::id();
        $now = Carbon::now('Asia/Kuala_Lumpur');
        $currentMonthStr = $now->format('Y-m');

        $wallets = Wallet::query()
            ->with(['transactions', 'incomingTransfers'])
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $totalBalance = (float) $wallets->sum(fn (Wallet $w): float => $w->currentBalance());

        $monthExpense = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $now->year)
            ->whereMonth('transaction_date', $now->month)
            ->sum('amount');

        $totalBudget = (float) Budget::query()
            ->where('user_id', $userId)
            ->where('month_year', $currentMonthStr)
            ->sum('amount');

        $budgetPct = $totalBudget > 0 ? round(($monthExpense / $totalBudget) * 100, 1) : 0.0;

        return [
            'total_balance' => round($totalBalance, 2),
            'month_expense' => round($monthExpense, 2),
            'total_budget' => round($totalBudget, 2),
            'budget_percentage' => $budgetPct,
            'wallets' => $wallets,
        ];
    }

    /**
     * Active goals and their progress.
     *
     * @return Collection<int, Goal>
     */
    #[Computed]
    public function activeGoals(): Collection
    {
        return Goal::query()
            ->with('milestones')
            ->where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->orderBy('target_date')
            ->limit(4)
            ->get();
    }

    /**
     * Subscriptions that are due soon (next 7 days).
     *
     * @return Collection<int, Subscription>
     */
    #[Computed]
    public function upcomingSubscriptions(): Collection
    {
        $user = Auth::user();
        if ($user === null) {
            return new Collection;
        }

        return Subscription::query()
            ->with('wallet')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('next_billing_date')
            ->get()
            ->filter(fn (Subscription $sub): bool => $sub->isDueSoon())
            ->values()
            ->take(4);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
