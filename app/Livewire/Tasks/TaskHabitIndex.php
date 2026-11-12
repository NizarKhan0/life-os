<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Task;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tasks & Habits Tracker — LifeOS')]
class TaskHabitIndex extends Component
{
    public string $taskFilter = 'today'; // 'today' | 'important' | 'all'

    // Quick Add Task State (Press Enter)
    public string $quickTaskTitle = '';

    public string $quickTaskDescription = '';

    public string $quickTaskPriority = 'medium';

    public string $quickTaskDueDate = '';

    // Edit Task Modal State
    public bool $showTaskModal = false;

    public ?int $editingTaskId = null;

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskPriority = 'medium';

    public string $taskDueDate = '';

    // Habit Modal State
    public bool $showHabitModal = false;

    public string $habitName = '';

    public string $habitFrequency = 'daily';

    public int $habitTargetPerDay = 1;

    public string $habitColor = 'emerald';

    public function mount(): void
    {
        $this->quickTaskDueDate = Carbon::today()->toDateString();
    }

    /**
     * @return Collection<int, Task>
     */
    #[Computed]
    public function tasks(): Collection
    {
        $today = Carbon::today();

        return Task::query()
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'archived')
            ->when($this->taskFilter === 'today', function ($query) use ($today): void {
                $query->where(function ($sub) use ($today): void {
                    $sub->whereDate('due_date', '<=', $today)
                        ->orWhereNull('due_date');
                });
            })
            ->when($this->taskFilter === 'important', function ($query): void {
                $query->whereIn('priority', ['high', 'urgent']);
            })
            ->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END")
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->latest('id')
            ->get();
    }

    /**
     * @return Collection<int, Habit>
     */
    #[Computed]
    public function habits(): Collection
    {
        return Habit::query()
            ->with('logs')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function setTaskFilter(string $filter): void
    {
        if (in_array($filter, ['today', 'important', 'all'], true)) {
            $this->taskFilter = $filter;
            unset($this->tasks);
        }
    }

    /**
     * Quick add task when pressing Enter in the input bar.
     */
    public function quickAddTask(): void
    {
        $title = trim($this->quickTaskTitle);

        if ($title === '') {
            return;
        }

        $validated = $this->validate([
            'quickTaskTitle' => ['required', 'string', 'max:255'],
            'quickTaskDescription' => ['nullable', 'string', 'max:1000'],
            'quickTaskPriority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'quickTaskDueDate' => ['nullable', 'date'],
        ]);

        Task::query()->create([
            'user_id' => (int) Auth::id(),
            'title' => $validated['quickTaskTitle'],
            'description' => trim((string) $validated['quickTaskDescription']) !== ''
                ? trim((string) $validated['quickTaskDescription'])
                : null,
            'priority' => $validated['quickTaskPriority'],
            'status' => 'todo',
            'due_date' => $validated['quickTaskDueDate'] !== '' ? $validated['quickTaskDueDate'] : Carbon::today()->toDateString(),
        ]);

        $this->quickTaskTitle = '';
        $this->quickTaskDescription = '';
        unset($this->tasks);

        Flux::toast(variant: 'success', text: 'Tugasan baharu ditambah.');
    }

    public function editTask(int $taskId): void
    {
        $task = Task::query()
            ->where('user_id', Auth::id())
            ->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->taskTitle = $task->title;
        $this->taskDescription = (string) ($task->description ?? '');
        $this->taskPriority = $task->priority;
        $this->taskDueDate = $task->due_date ? $task->due_date->format('Y-m-d') : '';
        $this->showTaskModal = true;
    }

    public function updateTask(): void
    {
        if ($this->editingTaskId === null) {
            return;
        }

        $validated = $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['nullable', 'string', 'max:1000'],
            'taskPriority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'taskDueDate' => ['nullable', 'date'],
        ]);

        $task = Task::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->editingTaskId);

        $task->update([
            'title' => $validated['taskTitle'],
            'description' => trim((string) $validated['taskDescription']) !== ''
                ? trim((string) $validated['taskDescription'])
                : null,
            'priority' => $validated['taskPriority'],
            'due_date' => $validated['taskDueDate'] !== '' ? $validated['taskDueDate'] : null,
        ]);

        $this->showTaskModal = false;
        $this->editingTaskId = null;
        unset($this->tasks);

        Flux::toast(variant: 'success', text: 'Tugasan berjaya dikemas kini.');
    }

    /**
     * One-click checkbox toggle for a Task.
     */
    public function toggleTask(int $taskId): void
    {
        $task = Task::query()
            ->where('user_id', Auth::id())
            ->findOrFail($taskId);

        $isCompleting = $task->status !== 'completed';

        $task->update([
            'status' => $isCompleting ? 'completed' : 'todo',
            'completed_at' => $isCompleting ? Carbon::now() : null,
        ]);

        unset($this->tasks);

        if ($isCompleting) {
            Flux::toast(variant: 'success', text: 'Tugasan telah diselesaikan.');
        } else {
            Flux::toast(variant: 'warning', text: 'Tugasan dibuka semula.');
        }
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::query()
            ->where('user_id', Auth::id())
            ->findOrFail($taskId);

        $task->delete();
        unset($this->tasks);

        Flux::toast(variant: 'success', text: 'Tugasan telah dipadam.');
    }

    /**
     * One-click status circle toggle for a Habit today.
     */
    public function toggleHabitToday(int $habitId): void
    {
        $habit = Habit::query()
            ->with('logs')
            ->where('user_id', Auth::id())
            ->findOrFail($habitId);

        $todayStr = Carbon::today()->toDateString();

        $existingLog = HabitLog::query()
            ->where('habit_id', $habit->id)
            ->whereDate('logged_date', $todayStr)
            ->first();

        if ($existingLog !== null) {
            $existingLog->delete();
            Flux::toast(variant: 'warning', text: 'Tanda habit hari ini dibatalkan.');
        } else {
            HabitLog::query()->create([
                'habit_id' => $habit->id,
                'logged_date' => $todayStr,
                'count' => max(1, $habit->target_per_day),
            ]);
            Flux::toast(variant: 'success', text: 'Habit hari ini telah diselesaikan! 🔥');
        }

        unset($this->habits);
    }

    public function openHabitModal(): void
    {
        $this->resetValidation();
        $this->habitName = '';
        $this->habitFrequency = 'daily';
        $this->habitTargetPerDay = 1;
        $this->habitColor = 'emerald';
        $this->showHabitModal = true;
    }

    public function saveHabit(): void
    {
        $validated = $this->validate([
            'habitName' => ['required', 'string', 'max:255'],
            'habitFrequency' => ['required', 'string', 'in:daily,weekdays,weekly'],
            'habitTargetPerDay' => ['required', 'integer', 'min:1', 'max:20'],
            'habitColor' => ['required', 'string', 'in:emerald,sky,violet,amber,rose'],
        ]);

        Habit::query()->create([
            'user_id' => (int) Auth::id(),
            'name' => $validated['habitName'],
            'frequency' => $validated['habitFrequency'],
            'target_per_day' => (int) $validated['habitTargetPerDay'],
            'color' => $validated['habitColor'],
            'is_active' => true,
        ]);

        unset($this->habits);
        $this->showHabitModal = false;

        Flux::toast(variant: 'success', text: 'Habit baharu berjaya dicipta.');
    }

    public function deleteHabit(int $habitId): void
    {
        $habit = Habit::query()
            ->where('user_id', Auth::id())
            ->findOrFail($habitId);

        $habit->delete();
        unset($this->habits);

        Flux::toast(variant: 'success', text: 'Habit telah dipadam.');
    }

    public function sendTelegramSummary(TelegramService $telegramService): void
    {
        $pendingTasks = $this->tasks()->where('status', '!=', 'completed')->values();
        $uncompletedHabits = $this->habits()->filter(fn (Habit $h): bool => ! $h->isCompletedToday())->values();

        if ($telegramService->sendTaskHabitReminder($pendingTasks, $uncompletedHabits)) {
            Flux::toast(variant: 'success', text: 'Ringkasan Tasks & Habits dihantar ke Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Simulasi berjaya (Tetapkan TELEGRAM_BOT_TOKEN dalam .env untuk mesej sebenar).');
        }
    }

    public function render(): View
    {
        return view('livewire.tasks.task-habit-index');
    }
}
