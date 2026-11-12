<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Goal;
use App\Models\Milestone;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
    protected $description = 'Hantar ringkasan harian LifeOS (Goals & Milestones) ke Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService): int
    {
        $activeGoals = Goal::query()
            ->with('milestones')
            ->where('status', 'in_progress')
            ->orderBy('target_date')
            ->get();

        $dueMilestones = Milestone::query()
            ->with('goal')
            ->where('is_completed', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', Carbon::now()->addDays(7))
            ->orderBy('due_date')
            ->get();

        $dateFormatted = Carbon::now('Asia/Kuala_Lumpur')->translatedFormat('d M Y');

        $lines = [
            "☀️ <b>Ringkasan Harian LifeOS — {$dateFormatted}</b>",
            '',
            "🎯 <b>Matlamat Aktif ({$activeGoals->count()}):</b>",
        ];

        if ($activeGoals->isEmpty()) {
            $lines[] = '  • Tiada matlamat aktif.';
        } else {
            foreach ($activeGoals as $goal) {
                $progress = number_format($goal->progressPercentage(), 1);
                $days = $goal->daysRemaining();
                $daysBadge = $days !== null ? " ({$days}h lagi)" : '';
                $lines[] = '  • <b>'.e($goal->title)."</b> — {$progress}%{$daysBadge}";
            }
        }

        if ($dueMilestones->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '⚡ <b>Milestone Hampir Tarikh Akhir (7 Hari):</b>';

            foreach ($dueMilestones as $milestone) {
                $due = $milestone->due_date?->format('d/m/Y') ?? '-';
                $lines[] = '  • '.e($milestone->title)." <i>({$due})</i>";
            }
        }

        $telegramService->sendMessage(implode("\n", $lines));

        $this->info('Ringkasan harian LifeOS selesai diproses.');

        return self::SUCCESS;
    }
}
