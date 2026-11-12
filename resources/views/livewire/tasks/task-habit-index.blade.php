<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                ✅ Tasks & Habits Tracker
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Crush daily to-dos rapidly and build unstoppable consecutive day streaks.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="sendTelegramSummary"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300"
            >
                🔔 Send Telegram Alert
            </button>

            <button
                type="button"
                wire:click="openHabitModal"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                + Add Habit
            </button>
        </div>
    </div>

    {{-- Habits Section with Progress Bar & 7-Day Consistency Matrix --}}
    @php
        $totalHabits = $this->habits->count();
        $doneCount = $this->habits->filter(fn ($h) => $h->isCompletedToday())->count();
        $percentDone = $totalHabits > 0 ? (int) round(($doneCount / $totalHabits) * 100) : 0;
    @endphp

    <div class="relative overflow-hidden rounded-3xl border border-zinc-200/90 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
        {{-- Subtle ambient gradient aura in background --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-amber-500/10 blur-3xl"></div>

        {{-- Section Header with Interactive Progress --}}
        <div class="relative mb-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-sm font-bold text-amber-500">🔥</span>
                    <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Daily Discipline & Habit Matrix
                    </h2>
                </div>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    One-click daily check-in. Build compound momentum with unbroken 7-day streaks.
                </p>
            </div>

            <div class="flex flex-col items-start gap-1.5 sm:items-end">
                <div class="flex items-center gap-2.5">
                    <div class="h-2.5 w-32 sm:w-44 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            class="h-full rounded-full bg-linear-to-r from-amber-500 via-emerald-500 to-teal-400 transition-all duration-500 shadow-xs"
                            style="width: {{ $percentDone }}%"
                        ></div>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-black text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $percentDone }}%
                    </span>
                </div>
                <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ $doneCount }} of {{ $totalHabits }} completed today
                    @if ($totalHabits > 0 && $doneCount === $totalHabits)
                        • <span class="text-emerald-500 font-bold">✨ All targets hit!</span>
                    @endif
                </span>
            </div>
        </div>

        @if ($this->habits->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-200 py-8 text-center dark:border-zinc-800">
                <div class="mx-auto mb-2 flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-xl dark:bg-zinc-800">
                    🌱
                </div>
                <h4 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">No active daily habits yet</h4>
                <p class="mt-1 text-xs text-zinc-400">
                    Establish your daily rituals. Click "+ Add Habit" to kickstart your first streak.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->habits as $habit)
                    @php
                        $doneToday = $habit->isCompletedToday();
                        $streak = $habit->currentStreak();
                        $weeklyHistory = $habit->recentWeeklyHistory(7);

                        $frequencyLabel = match ($habit->frequency) {
                            'weekdays' => 'Weekdays',
                            'weekly' => 'Weekly',
                            default => 'Daily',
                        };
                    @endphp

                    <div
                        wire:key="habit-card-{{ $habit->id }}"
                        class="group relative flex flex-col justify-between gap-4 rounded-2xl border p-4.5 transition-all duration-200 {{ $doneToday ? 'border-emerald-500/50 bg-gradient-to-br from-emerald-500/10 via-zinc-900/60 to-zinc-900/90 shadow-md shadow-emerald-500/5 dark:border-emerald-500/40' : 'border-zinc-200 bg-zinc-50/60 hover:-translate-y-0.5 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-950/60 dark:hover:border-zinc-700' }}"
                    >
                        {{-- Top Header inside Card --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                {{-- Reactive Large Check Circle --}}
                                <button
                                    type="button"
                                    wire:click="toggleHabitToday({{ $habit->id }})"
                                    title="{{ $doneToday ? 'Click to mark as incomplete' : 'Click to complete for today' }}"
                                    class="group/btn relative flex size-11 shrink-0 cursor-pointer items-center justify-center rounded-2xl transition-all duration-200 {{ $doneToday ? 'bg-gradient-to-tr from-emerald-600 to-emerald-400 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-400/30 scale-100' : 'border-2 border-dashed border-zinc-300 bg-white text-zinc-400 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-600 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:border-emerald-400 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-400' }}"
                                >
                                    @if ($doneToday)
                                        <svg class="size-6 transition-transform group-hover/btn:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg class="size-5 transition-transform group-hover/btn:scale-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    @endif
                                </button>

                                <div>
                                    <h3 class="text-sm font-bold tracking-tight {{ $doneToday ? 'text-emerald-950 dark:text-emerald-100' : 'text-zinc-900 dark:text-zinc-100' }}">
                                        {{ $habit->name }}
                                    </h3>
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <span class="rounded-md bg-zinc-200/80 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            {{ $frequencyLabel }}
                                        </span>
                                        @if ($doneToday)
                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                ✓ Completed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Streak Pill & Delete Button --}}
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 rounded-xl border border-amber-500/30 bg-gradient-to-r from-amber-500/15 to-orange-500/15 px-2.5 py-1 text-xs font-black text-amber-600 shadow-xs dark:text-amber-400">
                                    🔥 {{ $streak }}d
                                </span>

                                <button
                                    type="button"
                                    wire:click="editHabit({{ $habit->id }})"
                                    class="opacity-0 transition-opacity rounded-lg p-1 text-zinc-400 hover:bg-zinc-200/60 hover:text-zinc-700 group-hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                    title="Edit Habit"
                                >
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    wire:click="confirmDeleteHabit({{ $habit->id }})"
                                    class="opacity-0 transition-opacity rounded-lg p-1 text-zinc-400 hover:bg-zinc-200/60 hover:text-rose-500 group-hover:opacity-100 dark:hover:bg-zinc-800"
                                    title="Delete Habit"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- 7-Day Rolling Consistency Dots --}}
                        <div class="flex items-center justify-between border-t border-zinc-200/70 pt-3 dark:border-zinc-800/80">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                                7-Day Matrix
                            </span>

                            <div class="flex items-center gap-1.5">
                                @foreach ($weeklyHistory as $day)
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-[9px] font-semibold {{ $day['is_today'] ? 'text-amber-500 dark:text-amber-400 font-bold' : 'text-zinc-400' }}">
                                            {{ $day['day_short'] }}
                                        </span>
                                        <span
                                            title="{{ $day['day'] }} ({{ $day['date'] }}): {{ $day['completed'] ? 'Completed' : 'Missed' }}"
                                            class="flex size-5 items-center justify-center rounded-md text-[9px] font-bold transition-all {{ $day['completed'] ? 'bg-emerald-500 text-white shadow-xs shadow-emerald-500/40' : ($day['is_today'] ? 'border border-dashed border-amber-500/60 bg-amber-500/10 text-amber-500' : 'bg-zinc-200/80 text-transparent dark:bg-zinc-800') }}"
                                        >
                                            @if ($day['completed'])
                                                ✓
                                            @elseif ($day['is_today'])
                                                •
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Tasks Section --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        {{-- Filter Tabs: Today / Important / All --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
            <div class="flex items-center gap-1.5">
                @php
                    $filters = [
                        'today' => '📅 Today',
                        'important' => '⚡ High Priority',
                        'all' => '📋 All Tasks',
                    ];
                @endphp

                @foreach ($filters as $key => $label)
                    <button
                        type="button"
                        wire:click="setTaskFilter('{{ $key }}')"
                        class="cursor-pointer rounded-xl px-3.5 py-2 text-xs font-bold transition {{ $taskFilter === $key ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                {{ $this->tasks->total() }} tasks
            </span>
        </div>

        {{-- Quick Task Input (Press Enter) --}}
        <form wire:submit="quickAddTask" class="mt-4 flex flex-col gap-2.5 rounded-xl border border-zinc-200/80 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/30">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <input
                    type="text"
                    wire:model="quickTaskTitle"
                    placeholder="New task title * (Press Enter ↵)"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                />
                <input
                    type="text"
                    wire:model="quickTaskDescription"
                    placeholder="Optional details / description..."
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                />
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <select
                    wire:model="quickTaskPriority"
                    class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent 🔥</option>
                </select>

                <input
                    type="date"
                    wire:model="quickTaskDueDate"
                    class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                />

                <button
                    type="submit"
                    class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    + Add Task
                </button>
            </div>
        </form>

        {{-- Tasks List --}}
        <ul class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($this->tasks as $task)
                @php
                    $isCompleted = $task->status === 'completed';

                    $priorityBadge = match ($task->priority) {
                        'urgent' => 'bg-rose-500/15 text-rose-700 ring-rose-500/30 dark:text-rose-300',
                        'high' => 'bg-amber-500/15 text-amber-700 ring-amber-500/30 dark:text-amber-300',
                        'medium' => 'bg-sky-500/15 text-sky-700 ring-sky-500/30 dark:text-sky-300',
                        default => 'bg-zinc-500/10 text-zinc-600 ring-zinc-500/20 dark:text-zinc-400',
                    };
                @endphp

                <li
                    wire:key="task-item-{{ $task->id }}"
                    class="group flex items-center justify-between gap-3 py-3 transition hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30"
                >
                    <label class="flex flex-1 cursor-pointer items-center gap-3">
                        <input
                            type="checkbox"
                            wire:click="toggleTask({{ $task->id }})"
                            @checked($isCompleted)
                            class="size-4.5 cursor-pointer rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-800"
                        />

                        <div class="flex flex-col">
                            <span class="text-sm font-semibold {{ $isCompleted ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                                {{ $task->title }}
                            </span>
                            @if ($task->description)
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ $task->description }}
                                </span>
                            @endif
                        </div>
                    </label>

                    <div class="flex items-center gap-2">
                        @if ($task->due_date)
                            <span class="text-xs font-medium {{ ! $isCompleted && $task->due_date->isPast() && ! $task->due_date->isToday() ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                                {{ $task->due_date->isToday() ? 'Today' : $task->due_date->format('d M') }}
                            </span>
                        @endif

                        <span class="rounded-md px-2 py-0.5 text-[11px] font-bold uppercase ring-1 ring-inset {{ $priorityBadge }}">
                            {{ $task->priority }}
                        </span>

                        <button
                            type="button"
                            wire:click="editTask({{ $task->id }})"
                            class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-zinc-700 group-hover:visible dark:hover:text-zinc-200"
                            title="Edit Task"
                        >
                            ✏️
                        </button>

                        <button
                            type="button"
                            wire:click="confirmDeleteTask({{ $task->id }})"
                            class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-rose-600 group-hover:visible"
                            title="Delete Task"
                        >
                            🗑️
                        </button>
                    </div>
                </li>
            @empty
                <li class="py-10 text-center text-xs italic text-zinc-400">
                    No tasks found in this view. Type in the input field above and press Enter!
                </li>
            @endforelse
        </ul>

        @if ($this->tasks->hasPages())
            <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                {{ $this->tasks->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Edit Task --}}
    @if ($showTaskModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Update Task</h3>
                    <button type="button" wire:click="$set('showTaskModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="updateTask" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Task Title *</label>
                        <input
                            type="text"
                            wire:model="taskTitle"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('taskTitle') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Details / Description</label>
                        <textarea
                            wire:model="taskDescription"
                            rows="2"
                            placeholder="Add task notes or extra context..."
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Priority</label>
                            <select
                                wire:model="taskPriority"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent 🔥</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Due Date</label>
                            <input
                                type="date"
                                wire:model="taskDueDate"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showTaskModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Add Habit --}}
    @if ($showHabitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $editingHabitId ? 'Edit Habit' : 'Add New Habit' }}</h3>
                    <button type="button" wire:click="$set('showHabitModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveHabit" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Habit Name *</label>
                        <input
                            type="text"
                            wire:model="habitName"
                            placeholder="e.g. 30 Minutes Gym / Read 10 Pages"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('habitName') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Frequency</label>
                            <select
                                wire:model="habitFrequency"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="daily">Daily</option>
                                <option value="weekdays">Weekdays (Mon - Fri)</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Daily Target</label>
                            <input
                                type="number"
                                min="1"
                                max="20"
                                wire:model="habitTargetPerDay"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showHabitModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">{{ $editingHabitId ? 'Update Habit' : 'Save Habit' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Flux Confirmation Delete Modal --}}
    <flux:modal wire:model.self="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Confirm Deletion</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this {{ $deletingType === 'task' ? 'task' : 'habit' }}? This action cannot be undone.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDelete">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
