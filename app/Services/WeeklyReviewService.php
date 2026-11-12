<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Goal;
use App\Models\HabitLog;
use App\Models\Meeting;
use App\Models\Task;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class WeeklyReviewService
{
    /**
     * Get the Monday (start) of the week for the given date.
     */
    public function weekStart(?CarbonInterface $date = null): CarbonInterface
    {
        return ($date ?? Carbon::now('Asia/Kuala_Lumpur'))->copy()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
    }

    /**
     * Build an automatic metrics snapshot for a user's week.
     *
     * @return array{tasks_completed: int, tasks_pending: int, habit_checkins: int, total_expense: float, total_income: float, meetings_held: int, goals_completed: int}
     */
    public function buildSnapshot(int $userId, CarbonInterface $weekStart): array
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $weekStart->copy()->endOfWeek(CarbonInterface::SUNDAY)->endOfDay();

        $tasksCompleted = Task::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->count();

        $tasksPending = Task::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['todo', 'in_progress'])
            ->whereDate('due_date', '<=', $end->toDateString())
            ->count();

        $habitCheckins = HabitLog::query()
            ->whereHas('habit', fn ($query) => $query->where('user_id', $userId))
            ->whereBetween('logged_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $totalExpense = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $totalIncome = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $meetingsHeld = Meeting::query()
            ->where('user_id', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $goalsCompleted = Goal::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        return [
            'tasks_completed' => $tasksCompleted,
            'tasks_pending' => $tasksPending,
            'habit_checkins' => $habitCheckins,
            'total_expense' => round($totalExpense, 2),
            'total_income' => round($totalIncome, 2),
            'meetings_held' => $meetingsHeld,
            'goals_completed' => $goalsCompleted,
        ];
    }
}
