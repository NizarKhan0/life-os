<?php

declare(strict_types=1);

namespace App\Livewire\Automation;

use App\Models\AutomationLog;
use App\Models\Goal;
use App\Models\Habit;
use App\Models\Meeting;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BriefingService;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Automation & Morning Briefing Hub')]
class AutomationHub extends Component
{
    use WithPagination;

    public ?int $selectedLogId = null;

    public bool $showDeleteModal = false;

    public ?int $deletingLogId = null;

    /**
     * Send a manual test Morning Briefing to Telegram via BriefingService.
     */
    public function sendTestBriefing(BriefingService $briefingService, TelegramService $telegramService): void
    {
        /** @var User $user */
        $user = Auth::user();

        $log = $briefingService->dispatchBriefingForUser($user, $telegramService, 'manual');
        $this->selectedLogId = $log->id;

        unset($this->logs);

        if ($log->status === 'sent') {
            Flux::toast(
                text: 'Morning Briefing report sent directly to your Telegram!',
                heading: 'Briefing Sent',
                variant: 'success',
            );

            return;
        }

        if ($log->status === 'simulated') {
            Flux::toast(
                text: 'Briefing generated & recorded in log (Telegram token not configured in .env).',
                heading: 'Briefing Simulation Recorded',
                variant: 'warning',
            );

            return;
        }

        Flux::toast(
            text: 'Failed to send message via Telegram API. Please check token & chat ID configuration.',
            heading: 'Telegram Error',
            variant: 'danger',
        );
    }

    /**
     * Trigger an existing Artisan reminder command manually for testing.
     */
    public function triggerArtisanCommand(string $command): void
    {
        $briefingTime = (string) config('lifeos.schedules.briefing', '07:30');
        $goalsTime = (string) config('lifeos.schedules.goals', '08:00');
        $subsTime = (string) config('lifeos.schedules.subscriptions', '09:00');
        $budgetTime = (string) config('lifeos.schedules.budgets', '20:00');
        $tasksTime = (string) config('lifeos.schedules.tasks', '21:00');
        $reviewsTime = (string) config('lifeos.schedules.reviews', '20:00');

        $allowedCommands = [
            'lifeos:send-briefing' => "Morning Briefing ({$briefingTime})",
            'meetings:send-reminders' => 'Meeting Reminders (Every Minute)',
            'goals:send-reminders' => "Goal Reminders ({$goalsTime})",
            'subscriptions:check-due' => "Subscription Alerts ({$subsTime})",
            'money:check-budgets' => "Budget Threshold Alerts ({$budgetTime})",
            'tasks:send-reminders' => "Tasks & Habits Reminders ({$tasksTime})",
            'reviews:send-weekly-reminder' => "Weekly Review Reminder (Sunday {$reviewsTime})",
        ];

        if (! array_key_exists($command, $allowedCommands)) {
            return;
        }

        Artisan::call($command);

        /** @var User $user */
        $user = Auth::user();

        if ($command !== 'lifeos:send-briefing') {
            AutomationLog::query()->create([
                'user_id' => $user->id,
                'type' => $command,
                'channel' => 'telegram',
                'status' => app(TelegramService::class)->isConfigured() ? 'sent' : 'simulated',
                'triggered_by' => 'manual',
                'message_preview' => trim(Artisan::output()) !== ''
                    ? trim(Artisan::output())
                    : "Command {$command} was executed manually.",
                'sent_at' => now(),
            ]);
        }

        unset($this->logs);

        Flux::toast(
            text: "Command '{$allowedCommands[$command]}' completed successfully.",
            heading: 'Automation Executed',
            variant: 'success',
        );
    }

    /**
     * Select a log item to inspect its preview message.
     */
    public function selectLog(int $logId): void
    {
        $this->selectedLogId = $logId;
    }

    public function confirmDeleteLog(int $logId): void
    {
        $this->deletingLogId = $logId;
        $this->showDeleteModal = true;
    }

    public function executeDeleteLog(): void
    {
        if ($this->deletingLogId === null) {
            return;
        }

        $deleted = AutomationLog::query()
            ->where('user_id', Auth::id())
            ->whereKey($this->deletingLogId)
            ->delete();

        if ($deleted) {
            if ($this->selectedLogId === $this->deletingLogId) {
                $this->selectedLogId = null;
            }

            unset($this->logs);

            Flux::toast(
                text: 'Automation log record deleted.',
                heading: 'Log Deleted',
                variant: 'danger',
            );
        }

        $this->deletingLogId = null;
        $this->showDeleteModal = false;
    }

    /**
     * Delete an automation log entry.
     */
    public function deleteLog(int $logId): void
    {
        $this->confirmDeleteLog($logId);
    }

    /**
     * Get live Morning Briefing preview HTML for the current user.
     */
    #[Computed]
    public function briefingPreview(): string
    {
        /** @var User $user */
        $user = Auth::user();

        return app(BriefingService::class)->generateForUser($user);
    }

    /**
     * Get structured summary data for the dashboard cards.
     *
     * @return array{
     *     date_formatted: string,
     *     meetings: Collection<int, Meeting>,
     *     top_tasks: Collection<int, Task>,
     *     unlogged_habits: Collection<int, Habit>,
     *     yesterday_expense: float,
     *     total_wallet_balance: float,
     *     wallets: Collection<int, Wallet>,
     *     active_goals_count: int,
     *     upcoming_subscriptions_count: int,
     *     telegram_configured: bool
     * }
     */
    #[Computed]
    public function summary(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $data = app(BriefingService::class)->summaryData($user);

        $activeGoalsCount = Goal::query()
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $upcomingSubsCount = Subscription::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Subscription $sub): bool => $sub->isDueSoon())
            ->count();

        return [
            ...$data,
            'active_goals_count' => $activeGoalsCount,
            'upcoming_subscriptions_count' => $upcomingSubsCount,
            'telegram_configured' => app(TelegramService::class)->isConfigured(),
        ];
    }

    /**
     * Get recent automation logs for the current user.
     *
     * @return LengthAwarePaginator<int, AutomationLog>
     */
    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        return AutomationLog::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.automation.automation-hub');
    }
}
