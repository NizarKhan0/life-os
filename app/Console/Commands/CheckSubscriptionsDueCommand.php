<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class CheckSubscriptionsDueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-due {--days=3 : Bilangan hari sebelum tarikh bil untuk dihantar amaran}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Semak komitmen dan langganan aktif yang luput dalam 3 hari atau hari ini dan hantar amaran via TelegramService';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $daysThreshold = max(0, (int) $this->option('days'));

        $subscriptions = Subscription::query()
            ->with('wallet')
            ->where('is_active', true)
            ->orderBy('next_billing_date')
            ->get()
            ->filter(fn (Subscription $sub): bool => $sub->isDueSoon($daysThreshold))
            ->values();

        if ($subscriptions->isEmpty()) {
            $this->info("Tiada langganan atau komitmen yang akan luput dalam tempoh {$daysThreshold} hari.");

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($subscriptions as $subscription) {
            if ($telegramService->sendSubscriptionDueAlert($subscription)) {
                $sentCount++;
            }
        }

        $this->info("Selesai menyemak {$subscriptions->count()} komitmen hampir tamat tempoh ({$sentCount} amaran Telegram dihantar).");

        return self::SUCCESS;
    }
}
