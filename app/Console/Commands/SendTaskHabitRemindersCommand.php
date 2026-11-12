<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Habit;
use App\Models\Task;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTaskHabitRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hantar peringatan Telegram bagi tugasan (Tasks) hari ini dan amalan (Habits) yang belum diselesaikan';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $today = Carbon::today();

        $pendingTasks = Task::query()
            ->whereIn('status', ['todo', 'in_progress'])
            ->where(function ($query) use ($today): void {
                $query->whereDate('due_date', '<=', $today)
                    ->orWhereIn('priority', ['high', 'urgent']);
            })
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->get();

        $activeHabits = Habit::query()
            ->with('logs')
            ->where('is_active', true)
            ->get()
            ->filter(fn (Habit $habit): bool => ! $habit->isCompletedToday())
            ->values();

        if ($pendingTasks->isEmpty() && $activeHabits->isEmpty()) {
            $this->info('Semua tugasan utama dan habit hari ini telah selesai!');

            return self::SUCCESS;
        }

        $sent = $telegramService->sendTaskHabitReminder($pendingTasks, $activeHabits);

        $this->info(
            "Selesai memproses {$pendingTasks->count()} task dan {$activeHabits->count()} habit ("
            .($sent ? '1 mesej dihantar' : 'simulasi log').').'
        );

        return self::SUCCESS;
    }
}
