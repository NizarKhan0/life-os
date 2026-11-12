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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Automation & Morning Briefing Hub')]
class AutomationHub extends Component
{
    public ?int $selectedLogId = null;

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
                text: 'Laporan Morning Briefing berjaya dihantar terus ke Telegram anda!',
                heading: 'Briefing Dihantar',
                variant: 'success',
            );

            return;
        }

        if ($log->status === 'simulated') {
            Flux::toast(
                text: 'Briefing dijana & direkodkan dalam log (Token Telegram belum ditetapkan dalam .env).',
                heading: 'Simulasi Briefing Berjaya',
                variant: 'warning',
            );

            return;
        }

        Flux::toast(
            text: 'Gagal menghantar mesej ke Telegram API. Sila semak konfigurasi token & chat ID.',
            heading: 'Ralat Telegram',
            variant: 'danger',
        );
    }

    /**
     * Trigger an existing Artisan reminder command manually for testing.
     */
    public function triggerArtisanCommand(string $command): void
    {
        $allowedCommands = [
            'lifeos:send-briefing' => 'Morning Briefing (07:30 AM)',
            'meetings:send-reminders' => 'Meeting Reminders (Setiap Minit)',
            'goals:send-reminders' => 'Goal Reminders (08:00 AM)',
            'subscriptions:check-due' => 'Subscription Alerts (09:00 AM)',
            'money:check-budgets' => 'Budget Threshold Alerts (08:00 PM)',
            'tasks:send-reminders' => 'Tasks & Habits Reminders (09:00 PM)',
            'reviews:send-weekly-reminder' => 'Weekly Review Reminder (Ahad 08:00 PM)',
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
                    : "Command {$command} telah dijalankan secara manual.",
                'sent_at' => now(),
            ]);
        }

        unset($this->logs);

        Flux::toast(
            text: "Command '{$allowedCommands[$command]}' selesai dijalankan.",
            heading: 'Automasi Dijalankan',
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

    /**
     * Delete an automation log entry.
     */
    public function deleteLog(int $logId): void
    {
        $deleted = AutomationLog::query()
            ->where('user_id', Auth::id())
            ->whereKey($logId)
            ->delete();

        if ($deleted) {
            if ($this->selectedLogId === $logId) {
                $this->selectedLogId = null;
            }

            unset($this->logs);

            Flux::toast(
                text: 'Rekod log automasi telah dipadamkan.',
                heading: 'Log Dipadam',
                variant: 'danger',
            );
        }
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
     * @return Collection<int, AutomationLog>
     */
    #[Computed]
    public function logs(): Collection
    {
        return AutomationLog::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.automation.automation-hub');
    }
}

