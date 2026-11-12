<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Budget;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckBudgetAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'money:check-budgets {--threshold=80 : Peratusan penggunaan bajet untuk dihantar amaran}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Semak penggunaan bajet bulanan semasa dan hantar amaran Telegram jika melebihi had peratusan';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $threshold = (float) $this->option('threshold');
        $currentMonthYear = Carbon::now('Asia/Kuala_Lumpur')->format('Y-m');

        $budgets = Budget::query()
            ->with('category')
            ->where('month_year', $currentMonthYear)
            ->get();

        $alertBudgets = $budgets->filter(
            fn (Budget $budget): bool => $budget->progressPercentage() >= $threshold
        );

        if ($alertBudgets->isEmpty()) {
            $this->info("Tiada bajet bulanan ({$currentMonthYear}) yang melebihi {$threshold}%.");

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($alertBudgets as $budget) {
            if ($telegramService->sendBudgetAlert($budget)) {
                $sentCount++;
            }
        }

        $this->info("Selesai menyemak {$alertBudgets->count()} bajet melebihi had ({$sentCount} amaran Telegram dihantar).");

        return self::SUCCESS;
    }
}

