<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BrainDump;
use App\Models\User;
use App\Models\WeeklyReview;
use App\Services\TelegramService;
use App\Services\WeeklyReviewService;
use Illuminate\Console\Command;

class SendWeeklyReviewReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:send-weekly-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hantar peringatan Weekly Review beserta snapshot metrik minggu semasa ke Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService, WeeklyReviewService $reviewService): int
    {
        $weekStart = $reviewService->weekStart();
        $processed = 0;

        foreach (User::query()->cursor() as $user) {
            $alreadyReviewed = WeeklyReview::query()
                ->where('user_id', $user->id)
                ->whereDate('week_start_date', $weekStart->toDateString())
                ->get()
                ->contains(fn (WeeklyReview $review): bool => $review->isComplete());

            if ($alreadyReviewed) {
                continue;
            }

            $snapshot = $reviewService->buildSnapshot($user->id, $weekStart);
            $unprocessedDumps = BrainDump::query()
                ->where('user_id', $user->id)
                ->where('is_processed', false)
                ->count();

            $telegramService->sendWeeklyReviewReminder($snapshot, $unprocessedDumps);
            $processed++;
        }

        $this->info("Peringatan Weekly Review diproses untuk {$processed} pengguna (minggu bermula {$weekStart->format('d M Y')}).");

        return self::SUCCESS;
    }
}
