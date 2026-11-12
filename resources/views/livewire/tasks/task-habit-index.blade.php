<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                ✅ Tasks & Habits Tracker
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Selesaikan tugasan harian dengan pantas dan kekalkan momentum habit berturut-turut (Streak).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="sendTelegramSummary"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300"
            >
                🔔 Hantar Peringatan Telegram
            </button>

            <button
                type="button"
                wire:click="openHabitModal"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                + Tambah Habit
            </button>
        </div>
    </div>

    {{-- Habits Row with Status Circle + Streak Badge --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    🔥 Habit Harian Anda
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Klik bulatan status untuk menanda siap hari ini secara reaktif.
                </p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $this->habits->filter(fn ($h) => $h->isCompletedToday())->count() }}/{{ $this->habits->count() }} Siap Hari Ini
            </span>
        </div>

        @if ($this->habits->isEmpty())
            <p class="py-4 text-center text-xs italic text-zinc-400">
                Belum ada habit aktif. Klik "+ Tambah Habit" untuk mula membina disiplin harian.
            </p>
        @else
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @foreach ($this->habits as $habit)
                    @php
                        $doneToday = $habit->isCompletedToday();
                        $streak = $habit->currentStreak();

                        $frequencyLabel = match ($habit->frequency) {
                            'weekdays' => 'Isnin - Jumaat',
                            'weekly' => 'Mingguan',
                            default => 'Setiap Hari',
                        };
                    @endphp

                    <div
                        wire:key="habit-row-{{ $habit->id }}"
                        class="group flex items-center justify-between gap-3 rounded-2xl border p-4 transition {{ $doneToday ? 'border-emerald-500/40 bg-emerald-50/40 dark:border-emerald-500/30 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50/50 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-800/40' }}"
                    >
                        <div class="flex items-center gap-3.5">
                            {{-- Status Circle Button --}}
                            <button
                                type="button"
                                wire:click="toggleHabitToday({{ $habit->id }})"
                                title="{{ $doneToday ? 'Tanda belum siap' : 'Tanda siap hari ini' }}"
                                class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-full border-2 transition-all {{ $doneToday ? 'border-emerald-500 bg-emerald-500 text-white shadow-sm' : 'border-zinc-300 bg-white text-transparent hover:border-emerald-400 dark:border-zinc-600 dark:bg-zinc-900' }}"
                            >
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </button>

                            <div>
                                <h3 class="text-sm font-bold {{ $doneToday ? 'text-emerald-900 dark:text-emerald-200' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $habit->name }}
                                </h3>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $frequencyLabel }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Streak Badge (🔥 X hari) --}}
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-500/30 dark:text-amber-300">
                                🔥 {{ $streak }} hari
                            </span>

                            <button
                                type="button"
                                wire:click="deleteHabit({{ $habit->id }})"
                                wire:confirm="Padam habit ini?"
                                class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-rose-500 group-hover:visible"
                                title="Padam Habit"
                            >
                                &times;
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Tasks Section --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        {{-- Filter Tabs: Hari Ini / Penting / Semua --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800">
            <div class="flex items-center gap-1.5">
                @php
                    $filters = [
                        'today' => '📅 Hari Ini',
                        'important' => '⚡ Penting',
                        'all' => '📋 Semua',
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
                {{ $this->tasks->where('status', '!=', 'completed')->count() }} tugasan aktif
            </span>
        </div>

        {{-- Quick Task Input (Press Enter) --}}
        <form wire:submit="quickAddTask" class="mt-4 flex flex-col gap-2.5 rounded-xl border border-zinc-200/80 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/30">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <input
                    type="text"
                    wire:model="quickTaskTitle"
                    placeholder="Tajuk tugasan baharu * (Tekan Enter ↵)"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                />
                <input
                    type="text"
                    wire:model="quickTaskDescription"
                    placeholder="Detail / penerangan ringkas (Pilihan)..."
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
                    + Tambah Tugasan
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
                                {{ $task->due_date->isToday() ? 'Hari Ini' : $task->due_date->format('d M') }}
                            </span>
                        @endif

                        <span class="rounded-md px-2 py-0.5 text-[11px] font-bold uppercase ring-1 ring-inset {{ $priorityBadge }}">
                            {{ $task->priority }}
                        </span>

                        <button
                            type="button"
                            wire:click="editTask({{ $task->id }})"
                            class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-zinc-700 group-hover:visible dark:hover:text-zinc-200"
                            title="Edit Tugasan"
                        >
                            ✏️
                        </button>

                        <button
                            type="button"
                            wire:click="deleteTask({{ $task->id }})"
                            class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-rose-600 group-hover:visible"
                            title="Padam Tugasan"
                        >
                            🗑️
                        </button>
                    </div>
                </li>
            @empty
                <li class="py-10 text-center text-xs italic text-zinc-400">
                    Tiada tugasan dalam senarai ini. Taip di ruang atas dan tekan Enter untuk menambah!
                </li>
            @endforelse
        </ul>
    </div>

    {{-- Modal Edit Task --}}
    @if ($showTaskModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Kemaskini Tugasan</h3>
                    <button type="button" wire:click="$set('showTaskModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="updateTask" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tajuk Tugasan *</label>
                        <input
                            type="text"
                            wire:model="taskTitle"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('taskTitle') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Detail / Penerangan</label>
                        <textarea
                            wire:model="taskDescription"
                            rows="2"
                            placeholder="Tambah penerangan atau nota tugasan..."
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Prioriti</label>
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
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tarikh Akhir</label>
                            <input
                                type="date"
                                wire:model="taskDueDate"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showTaskModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Batal</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">Simpan Perubahan</button>
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
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Tambah Habit Baharu</h3>
                    <button type="button" wire:click="$set('showHabitModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveHabit" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Nama Habit *</label>
                        <input
                            type="text"
                            wire:model="habitName"
                            placeholder="Contoh: Senaman 30 Minit / Baca 10 Muka Surat"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('habitName') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Kekerapan</label>
                            <select
                                wire:model="habitFrequency"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="daily">Setiap Hari (Daily)</option>
                                <option value="weekdays">Hari Bekerja (Weekdays)</option>
                                <option value="weekly">Mingguan (Weekly)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Sasaran Sehari</label>
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
                        <button type="button" wire:click="$set('showHabitModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Batal</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">Simpan Habit</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

