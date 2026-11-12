<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BriefingService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendDailyBriefingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lifeos:send-briefing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jana dan hantar laporan Morning Briefing harian LifeOS ke Telegram';

    /**
     * Execute the console command.
     */
    public function handle(BriefingService $briefingService, TelegramService $telegramService): int
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $this->info('Tiada pengguna ditemui untuk Morning Briefing.');

            return self::SUCCESS;
        }

        $dispatchedCount = 0;

        foreach ($users as $user) {
            $briefingService->dispatchBriefingForUser($user, $telegramService, 'scheduler');
            $dispatchedCount++;
        }

        $this->info("Morning Briefing LifeOS selesai diproses untuk {$dispatchedCount} pengguna.");

        return self::SUCCESS;
    }
}
