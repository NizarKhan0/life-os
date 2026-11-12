<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MeetingReminder;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendMeetingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Semak reminder mesyuarat yang belum dihantar dalam tetingkap masa start_time dan hantar melalui Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $now = Carbon::now();

        $pendingReminders = MeetingReminder::query()
            ->with('meeting')
            ->where('is_sent', false)
            ->whereHas('meeting', function ($query) use ($now): void {
                $query->where('status', 'scheduled')
                    ->where('start_time', '>=', $now->copy()->subMinutes(15));
            })
            ->get();

        $dueReminders = $pendingReminders->filter(
            fn (MeetingReminder $reminder): bool => $reminder->isDue($now)
        );

        if ($dueReminders->isEmpty()) {
            $this->info('Tiada peringatan mesyuarat yang perlu dihantar pada minit ini.');

            return self::SUCCESS;
        }

        $processedCount = 0;

        foreach ($dueReminders as $reminder) {
            $telegramService->sendMeetingReminder(
                $reminder->meeting,
                $reminder->remind_before_minutes
            );

            $reminder->update([
                'is_sent' => true,
                'sent_at' => $now,
            ]);

            $processedCount++;
        }

        $this->info("Berjaya menghantar {$processedCount} peringatan mesyuarat.");

        return self::SUCCESS;
    }
}
