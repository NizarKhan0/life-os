<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                🎯 Goals & Milestones
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Track financial targets, personal achievements, and step-by-step milestones reactively.
            </p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Goal
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 dark:border-zinc-700/80 dark:bg-zinc-900/60">
        <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            @php
                $statuses = [
                    'all' => 'All Status',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'paused' => 'Paused',
                ];
            @endphp
            @foreach ($statuses as $key => $label)
                <button
                    type="button"
                    wire:click="$set('filterStatus', '{{ $key }}')"
                    class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $filterStatus === $key ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-200/70 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <label for="filterCategory" class="text-xs font-medium text-zinc-500 dark:text-zinc-400 shrink-0">Category:</label>
            <select
                id="filterCategory"
                wire:model.live="filterCategory"
                class="w-full sm:w-auto rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
            >
                <option value="all">All Categories</option>
                <option value="personal">Personal</option>
                <option value="financial">Financial</option>
                <option value="career">Career</option>
                <option value="health">Health</option>
                <option value="spiritual">Spiritual</option>
            </select>
        </div>
    </div>

    {{-- Grid Cards --}}
    @if ($this->goals->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/50 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900/30">
            <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-zinc-200/70 text-xl dark:bg-zinc-800">
                🎯
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">No goals found</h3>
            <p class="mt-1 max-w-sm text-xs text-zinc-500 dark:text-zinc-400">
                Start your journey by setting your first financial or personal goal today.
            </p>
            <button
                type="button"
                wire:click="openCreateModal"
                class="mt-4 cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                + Create First Goal
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->goals as $goal)
                @php
                    $progress = $goal->progressPercentage();
                    $daysRemaining = $goal->daysRemaining();

                    $categoryBadge = match ($goal->category) {
                        'financial' => 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300',
                        'career' => 'bg-blue-500/10 text-blue-700 ring-blue-500/20 dark:text-blue-300',
                        'health' => 'bg-rose-500/10 text-rose-700 ring-rose-500/20 dark:text-rose-300',
                        'spiritual' => 'bg-amber-500/10 text-amber-700 ring-amber-500/20 dark:text-amber-300',
                        default => 'bg-violet-500/10 text-violet-700 ring-violet-500/20 dark:text-violet-300',
                    };

                    $statusBadge = match ($goal->status) {
                        'completed' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
                        'paused' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
                        default => 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
                    };

                    $statusLabel = match ($goal->status) {
                        'completed' => 'Completed',
                        'paused' => 'Paused',
                        default => 'In Progress',
                    };
                @endphp

                <div
                    wire:key="goal-card-{{ $goal->id }}"
                    class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-zinc-300 dark:border-zinc-700/80 dark:bg-zinc-900 dark:hover:border-zinc-600"
                >
                    <div>
                        {{-- Card Top Badges & Actions --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $categoryBadge }}">
                                    {{ ucfirst($goal->category) }}
                                </span>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="sendReminder({{ $goal->id }})"
                                    title="Send Telegram Alert"
                                    class="cursor-pointer rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-sky-500 dark:hover:bg-zinc-800"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="editGoal({{ $goal->id }})"
                                    title="Edit Goal"
                                    class="cursor-pointer rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDeleteGoal({{ $goal->id }})"
                                    title="Delete Goal"
                                    class="cursor-pointer rounded-lg p-1.5 text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Title & Description --}}
                        <h2 class="mt-3 text-base font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $goal->title }}
                        </h2>
                        @if ($goal->description)
                            <p class="mt-1 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $goal->description }}
                            </p>
                        @endif

                        {{-- Target Date & Days Remaining --}}
                        @if ($goal->target_date)
                            <div class="mt-3 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>📅 Target: {{ $goal->target_date->format('d M Y') }}</span>
                                <span class="font-medium {{ $daysRemaining !== null && $daysRemaining < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    @if ($daysRemaining < 0)
                                        Overdue by {{ abs($daysRemaining) }} days
                                    @elseif ($daysRemaining === 0)
                                        Due today!
                                    @else
                                        {{ $daysRemaining }} days remaining
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Progress Bar Tailwind v4 --}}
                        <div class="mt-4">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="font-medium text-zinc-600 dark:text-zinc-300">
                                    @if ($goal->target_value !== null && (float) $goal->target_value > 0)
                                        {{ $goal->unit ? $goal->unit.' ' : '' }}{{ number_format((float) $goal->current_value, 2) }}
                                        / {{ number_format((float) $goal->target_value, 2) }}
                                    @else
                                        Milestone Progress
                                    @endif
                                </span>
                                <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($progress, 1) }}%</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div
                                    class="h-full rounded-full bg-linear-to-r from-indigo-500 to-emerald-500 transition-all duration-500"
                                    style="width: {{ min(100, max(0, $progress)) }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Quick Progress Updater (for numeric target goals) --}}
                        @if ($goal->target_value !== null && (float) $goal->target_value > 0)
                            <form
                                wire:submit="updateProgress({{ $goal->id }})"
                                class="mt-3 flex items-center gap-2"
                            >
                                <div class="relative flex-1">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model="quickProgress.{{ $goal->id }}"
                                        placeholder="Current value"
                                        class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs text-zinc-900 focus:border-zinc-400 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-100"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="cursor-pointer rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                                >
                                    Update
                                </button>
                            </form>
                        @endif

                        {{-- Milestones Checklist --}}
                        <div class="mt-5 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                    Milestones ({{ $goal->milestones->where('is_completed', true)->count() }}/{{ $goal->milestones->count() }})
                                </span>
                            </div>

                            <ul class="space-y-1.5">
                                @forelse ($goal->milestones as $milestone)
                                    <li
                                        wire:key="milestone-{{ $milestone->id }}"
                                        class="group flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/60"
                                    >
                                        <label class="flex flex-1 cursor-pointer items-center gap-2.5 text-xs">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleMilestone({{ $milestone->id }})"
                                                @checked($milestone->is_completed)
                                                class="size-4 cursor-pointer rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500 dark:border-zinc-600 dark:bg-zinc-800"
                                            />
                                            <span class="{{ $milestone->is_completed ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-700 dark:text-zinc-200' }}">
                                                {{ $milestone->title }}
                                            </span>
                                        </label>

                                        <div class="flex items-center gap-1.5">
                                            @if ($milestone->due_date)
                                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500">
                                                    {{ $milestone->due_date->format('d/m') }}
                                                </span>
                                            @endif
                                            <button
                                                type="button"
                                                wire:click="deleteMilestone({{ $milestone->id }})"
                                                class="invisible cursor-pointer text-zinc-400 hover:text-rose-500 group-hover:visible"
                                                title="Delete milestone"
                                            >
                                                &times;
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="py-1 text-xs italic text-zinc-400 dark:text-zinc-500">
                                        No milestones yet.
                                    </li>
                                @endforelse
                            </ul>

                            {{-- Add Milestone Inline --}}
                            <form
                                wire:submit="addMilestone({{ $goal->id }})"
                                class="mt-2.5 flex items-center gap-1.5"
                            >
                                <input
                                    type="text"
                                    wire:model="newMilestoneTitle.{{ $goal->id }}"
                                    placeholder="+ Add milestone..."
                                    class="flex-1 rounded-lg border border-zinc-200 bg-transparent px-2.5 py-1 text-xs text-zinc-800 placeholder-zinc-400 focus:border-zinc-400 focus:outline-hidden dark:border-zinc-700 dark:text-zinc-200"
                                />
                                <button
                                    type="submit"
                                    class="cursor-pointer rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                >
                                    Add
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($this->goals->hasPages())
            <div class="mt-4 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                {{ $this->goals->links() }}
            </div>
        @endif
    @endif

    {{-- Modal Create / Edit Goal --}}
    @if ($showGoalModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $editingGoalId ? 'Update Goal' : 'Create New Goal' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeGoalModal"
                        class="cursor-pointer rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800"
                    >
                        ✕
                    </button>
                </div>

                <form wire:submit="saveGoal" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Goal Title *</label>
                        <input
                            type="text"
                            wire:model="title"
                            placeholder="e.g. Emergency Fund RM 10,000"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('title') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Description</label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            placeholder="Brief context on why this goal matters..."
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                        @error('description') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Category</label>
                            <select
                                wire:model="category"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="personal">Personal</option>
                                <option value="financial">Financial</option>
                                <option value="career">Career</option>
                                <option value="health">Health</option>
                                <option value="spiritual">Spiritual</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Target Date</label>
                            <input
                                type="date"
                                wire:model="target_date"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Target Value (Optional)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="target_value"
                                placeholder="10000"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Current Value</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="current_value"
                                placeholder="0"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Unit</label>
                            <input
                                type="text"
                                wire:model="unit"
                                placeholder="RM / km / books"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Status</label>
                        <select
                            wire:model="status"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="paused">Paused</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeGoalModal"
                            class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            {{ $editingGoalId ? 'Save Changes' : 'Create Goal' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Flux Confirmation Delete Modal --}}
    <flux:modal wire:model.self="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Goal</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this goal and all associated milestones? This action cannot be undone.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteGoal">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
