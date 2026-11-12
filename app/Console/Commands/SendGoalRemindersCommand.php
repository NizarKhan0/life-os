<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Goal;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendGoalRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goals:send-reminders {--days=7 : Bilangan hari sebelum tarikh sasaran untuk dihantar peringatan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hantar peringatan Telegram bagi matlamat (Goals) dan milestone yang hampir tamat tempoh';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $daysThreshold = max(1, (int) $this->option('days'));
        $cutoffDate = Carbon::now()->addDays($daysThreshold)->endOfDay();

        $goals = Goal::query()
            ->with('milestones')
            ->where('status', 'in_progress')
            ->where(function ($query) use ($cutoffDate): void {
                $query->whereNotNull('target_date')
                    ->whereDate('target_date', '<=', $cutoffDate)
                    ->orWhereHas('milestones', function ($milestoneQuery) use ($cutoffDate): void {
                        $milestoneQuery->where('is_completed', false)
                            ->whereNotNull('due_date')
                            ->whereDate('due_date', '<=', $cutoffDate);
                    });
            })
            ->get();

        if ($goals->isEmpty()) {
            $this->info('Tiada matlamat yang memerlukan peringatan buat masa ini.');

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($goals as $goal) {
            if ($telegramService->sendGoalReminder($goal)) {
                $sentCount++;
            }
        }

        $this->info("Selesai memproses {$goals->count()} matlamat ({$sentCount} mesej Telegram dihantar).");

        return self::SUCCESS;
    }
}
