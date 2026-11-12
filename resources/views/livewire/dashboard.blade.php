<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-6">
    @php
        $now = \Illuminate\Support\Carbon::now('Asia/Kuala_Lumpur');
        $financial = $this->financialSnapshot;
        $meetings = $this->todayMeetings;
        $tasks = $this->todayTasks;
        $habits = $this->habits;
        $goals = $this->activeGoals;
        $subscriptions = $this->upcomingSubscriptions;

        $hour = $now->hour;
        $greeting = match (true) {
            $hour < 12 => 'Good Morning',
            $hour < 18 => 'Good Afternoon',
            default => 'Good Evening',
        };
    @endphp

    {{-- Executive Header & Live Overview Banner --}}
    <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-gradient-to-br from-zinc-900 via-zinc-900 to-zinc-950 p-6 text-white shadow-xl dark:border-zinc-800">
        {{-- Subtle ambient aura circles in background --}}
        <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-zinc-700/80 bg-zinc-800/80 px-3.5 py-1 text-xs font-semibold backdrop-blur-md">
                    <span class="flex size-2">
                        <span class="absolute inline-flex h-2 w-2 animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                    </span>
                    <span class="text-zinc-300">{{ $greeting }}, {{ auth()->user()?->name ?? 'Executive' }}</span>
                    <span class="text-zinc-600">•</span>
                    <span class="text-zinc-400">{{ $now->translatedFormat('l, d F Y') }}</span>
                </div>

                <h1 class="text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl">
                    Executive <span class="bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 bg-clip-text text-transparent">Overview</span>
                </h1>
                <p class="text-xs sm:text-sm text-zinc-400">
                    Centralized daily command center for execution, strategic milestones, and cashflow intelligence.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button
                    type="button"
                    wire:click="sendQuickBriefing"
                    wire:loading.attr="disabled"
                    class="group relative inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-5 py-3 text-xs font-black text-zinc-950 shadow-lg shadow-amber-500/20 transition-all duration-200 hover:scale-[1.02] hover:from-amber-300 hover:to-amber-400 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="sendQuickBriefing" class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <span>Dispatch Telegram Briefing</span>
                    </span>
                    <span wire:loading wire:target="sendQuickBriefing" class="flex items-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Dispatching Alert...</span>
                    </span>
                </button>

                <a
                    href="{{ route('automation.index') }}"
                    wire:navigate
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-zinc-700 bg-zinc-800/90 px-4 py-3 text-xs font-bold text-zinc-200 backdrop-blur-md transition hover:bg-zinc-700 hover:text-white"
                >
                    <span>🤖 Automation Hub</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Top Row: High-level Executive KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Net Worth / Balance --}}
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/90">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Net Balance</span>
                <span class="flex size-9 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-base">💰</span>
            </div>
            <p class="mt-3 text-2xl font-black tracking-tight text-zinc-900 dark:text-white">
                RM {{ number_format($financial['total_balance'], 2) }}
            </p>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>Active across {{ $financial['wallets']->count() }} wallets</span>
            </div>
        </div>

        {{-- Monthly Expense vs Budget --}}
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/90">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Month Burn</span>
                <span class="flex size-9 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-base">💸</span>
            </div>
            <p class="mt-3 text-2xl font-black tracking-tight text-zinc-900 dark:text-white">
                RM {{ number_format($financial['month_expense'], 2) }}
            </p>
            <div class="mt-2">
                @if ($financial['total_budget'] > 0)
                    @php
                        $pct = $financial['budget_percentage'];
                        $barColor = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500');
                    @endphp
                    <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                        <span>{{ $pct }}% of budget</span>
                        <span>Limit: RM {{ number_format($financial['total_budget'], 0) }}</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                @else
                    <p class="text-xs text-zinc-400">No monthly limit set</p>
                @endif
            </div>
        </div>

        {{-- Meetings Today --}}
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/90">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Today's Agenda</span>
                <span class="flex size-9 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-base">📅</span>
            </div>
            <p class="mt-3 text-2xl font-black tracking-tight text-zinc-900 dark:text-white">
                {{ $meetings->count() }} {{ \Illuminate\Support\Str::plural('Session', $meetings->count()) }}
            </p>
            <p class="mt-2 text-xs font-semibold {{ $meetings->isNotEmpty() ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-400' }}">
                {{ $meetings->isNotEmpty() ? 'Next: '.$meetings->first()->start_time->format('h:i A') : 'Clear schedule today' }}
            </p>
        </div>

        {{-- Priority Tasks --}}
        <div class="relative overflow-hidden rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900/90">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Action Queue</span>
                <span class="flex size-9 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-base">⚡</span>
            </div>
            <p class="mt-3 text-2xl font-black tracking-tight text-zinc-900 dark:text-white">
                {{ $tasks->count() }} Tasks Pending
            </p>
            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                Top high & urgent priorities
            </p>
        </div>
    </div>

    {{-- Main Grid: Daily Action Hub --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Left Column (7 cols): Today's Execution Focus --}}
        <div class="flex flex-col gap-6 lg:col-span-7">
            {{-- Top Priority Tasks Section --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-rose-500/15 text-sm">🔥</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Priority Tasks</h2>
                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-xs font-bold text-rose-600 dark:text-rose-400">
                            {{ $tasks->count() }}
                        </span>
                    </div>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors">
                        View All Tasks →
                    </a>
                </div>

                <div class="mt-4 space-y-2.5">
                    @forelse ($tasks as $task)
                        <div
                            wire:key="dash-task-{{ $task->id }}"
                            class="group flex items-center justify-between gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/70 p-3.5 transition-all hover:border-zinc-300 dark:border-zinc-800/80 dark:bg-zinc-800/40 dark:hover:border-zinc-700"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <button
                                    type="button"
                                    wire:click="toggleTask({{ $task->id }})"
                                    class="flex size-6 shrink-0 cursor-pointer items-center justify-center rounded-lg border-2 transition-all {{ $task->status === 'completed' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-zinc-300 hover:border-emerald-500 dark:border-zinc-600' }}"
                                >
                                    @if ($task->status === 'completed')
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @endif
                                </button>
                                <div class="truncate">
                                    <p class="truncate text-xs font-bold text-zinc-900 dark:text-zinc-100 {{ $task->status === 'completed' ? 'line-through text-zinc-400 dark:text-zinc-500' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    @if ($task->due_date)
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                            Due: {{ $task->due_date->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <span class="shrink-0 rounded-lg px-2 py-0.5 text-[10px] font-black uppercase ring-1 ring-inset {{ $task->priority === 'urgent' ? 'bg-rose-500/15 text-rose-600 ring-rose-500/30 dark:text-rose-400' : ($task->priority === 'high' ? 'bg-amber-500/15 text-amber-600 ring-amber-500/30 dark:text-amber-400' : 'bg-blue-500/15 text-blue-600 ring-blue-500/30 dark:text-blue-400') }}">
                                {{ $task->priority }}
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
                            🎉 All priority tasks completed for today. Excellent execution!
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Today's Meetings Schedule --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-500/15 text-sm">📅</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Today's Schedule</h2>
                        <span class="rounded-full bg-indigo-500/15 px-2 py-0.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $meetings->count() }}
                        </span>
                    </div>
                    <a href="{{ route('meetings.index') }}" wire:navigate class="text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors">
                        Manage Meetings →
                    </a>
                </div>

                <div class="mt-4 space-y-2.5">
                    @forelse ($meetings as $meeting)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/70 p-3.5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-indigo-500/15 px-2 py-0.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $meeting->start_time->format('h:i A') }}
                                        @if ($meeting->end_time) - {{ $meeting->end_time->format('h:i A') }} @endif
                                    </span>
                                    <p class="truncate text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $meeting->title }}</p>
                                </div>
                                @if ($meeting->location_or_link)
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                                        📍 {{ $meeting->location_or_link }}
                                    </p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-lg bg-emerald-500/15 px-2 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                Scheduled
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500 dark:text-zinc-400">
                            No meetings scheduled today. Enjoy focused deep work time!
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Daily Habits (1-Click Streak Matrix) --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-sm">🔥</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Daily Habits Check-in</h2>
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                            {{ $habits->filter(fn ($h) => $h->isCompletedToday())->count() }}/{{ $habits->count() }} Done
                        </span>
                    </div>
                    <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors">
                        Habits Matrix →
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @forelse ($habits as $habit)
                        @php $completedToday = $habit->isCompletedToday(); @endphp
                        <div
                            wire:key="dash-habit-{{ $habit->id }}"
                            class="flex items-center justify-between gap-3 rounded-2xl border p-3.5 transition-all {{ $completedToday ? 'border-emerald-500/50 bg-emerald-500/10 shadow-xs' : 'border-zinc-200 bg-zinc-50/70 dark:border-zinc-800 dark:bg-zinc-800/40' }}"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold {{ $completedToday ? 'text-emerald-900 dark:text-emerald-100' : 'text-zinc-900 dark:text-zinc-100' }}">
                                    {{ $habit->name }}
                                </p>
                                <p class="text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                    🔥 {{ $habit->currentStreak() }}d streak
                                </p>
                            </div>

                            @if ($completedToday)
                                <span class="rounded-xl bg-emerald-500 px-2.5 py-1 text-xs font-black text-white shadow-xs">
                                    ✓ Done
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="checkInHabit({{ $habit->id }})"
                                    class="cursor-pointer rounded-xl bg-zinc-900 px-3 py-1.5 text-xs font-bold text-white shadow-xs transition hover:scale-105 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                                >
                                    Check-in
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-2 py-6 text-center text-xs text-zinc-500">No active habits. Create one in Tasks & Habits.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Strategic Intelligence & Commitments --}}
        <div class="flex flex-col gap-6 lg:col-span-5">
            {{-- Strategic Goals Progress --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-indigo-500/15 text-sm">🎯</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Strategic Goals</h2>
                        <span class="rounded-full bg-indigo-500/15 px-2 py-0.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $goals->count() }}
                        </span>
                    </div>
                    <a href="{{ route('goals.index') }}" wire:navigate class="text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors">
                        Manage Goals →
                    </a>
                </div>

                <div class="mt-4 space-y-3.5">
                    @forelse ($goals as $goal)
                        @php $progress = $goal->progressPercentage(); @endphp
                        <div class="rounded-2xl border border-zinc-100 bg-zinc-50/70 p-3.5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $goal->title }}</p>
                                <span class="rounded-md bg-indigo-500/15 px-1.5 py-0.5 text-xs font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($progress, 0) }}%
                                </span>
                            </div>
                            <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 via-indigo-500 to-emerald-500 transition-all duration-500" style="width: {{ min(100, $progress) }}%"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400 font-medium">
                                <span>{{ $goal->milestones->where('is_completed', true)->count() }}/{{ $goal->milestones->count() }} milestones</span>
                                <span>{{ $goal->daysRemaining() !== null ? $goal->daysRemaining().'d remaining' : 'Open target' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500">No active goals. Define your Q1-Q4 roadmap!</div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Subscriptions / Vault --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-sm">💳</span>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Upcoming Subscriptions</h2>
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                            {{ $subscriptions->count() }}
                        </span>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" wire:navigate class="text-xs font-semibold text-amber-500 hover:text-amber-400 transition-colors">
                        Commitment Vault →
                    </a>
                </div>

                <div class="mt-4 space-y-2.5">
                    @forelse ($subscriptions as $sub)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/70 p-3.5 dark:border-zinc-800/80 dark:bg-zinc-800/40">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $sub->name }}</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $sub->next_billing_date->format('d/m/Y') }} • <span class="font-bold text-zinc-800 dark:text-zinc-200">RM {{ number_format((float) $sub->amount, 2) }}</span>
                                </p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-amber-500/15 px-2 py-1 text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                {{ $sub->daysUntilDue() }}d left
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500">No renewals due in the next 7 days.</div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Command Hub --}}
            <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90 sm:p-6">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white mb-3">🚀 Executive Fast Track</h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('money.index') }}" wire:navigate class="flex items-center gap-2.5 rounded-2xl border border-zinc-100 bg-zinc-50/80 p-3 transition hover:border-zinc-300 hover:bg-zinc-100/80 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-zinc-700">
                        <span class="text-base">💰</span>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Money Engine</span>
                            <span class="text-[10px] text-zinc-400">Wallets & Cashflow</span>
                        </div>
                    </a>
                    <a href="{{ route('reviews.index') }}" wire:navigate class="flex items-center gap-2.5 rounded-2xl border border-zinc-100 bg-zinc-50/80 p-3 transition hover:border-zinc-300 hover:bg-zinc-100/80 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-zinc-700">
                        <span class="text-base">🧠</span>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Weekly Review</span>
                            <span class="text-[10px] text-zinc-400">Sunday Reflection</span>
                        </div>
                    </a>
                    <a href="{{ route('goals.index') }}" wire:navigate class="flex items-center gap-2.5 rounded-2xl border border-zinc-100 bg-zinc-50/80 p-3 transition hover:border-zinc-300 hover:bg-zinc-100/80 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-zinc-700">
                        <span class="text-base">🎯</span>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Goals & OKR</span>
                            <span class="text-[10px] text-zinc-400">Target Roadmaps</span>
                        </div>
                    </a>
                    <a href="{{ route('automation.index') }}" wire:navigate class="flex items-center gap-2.5 rounded-2xl border border-zinc-100 bg-zinc-50/80 p-3 transition hover:border-zinc-300 hover:bg-zinc-100/80 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-zinc-700">
                        <span class="text-base">🤖</span>
                        <div>
                            <span class="block font-bold text-zinc-900 dark:text-zinc-100">Automation</span>
                            <span class="text-[10px] text-zinc-400">Telegram Bot & Cron</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
