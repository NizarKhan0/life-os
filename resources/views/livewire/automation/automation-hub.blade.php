<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-6">
    @php
        $summary = $this->summary;
        $logs = $this->logs;
        $selectedLog = $this->selectedLogId
            ? $logs->firstWhere('id', $this->selectedLogId)
            : $logs->first();
    @endphp

    {{-- Top Hero Banner: Morning Briefing & Manual Trigger --}}
    <div class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/90 via-zinc-900 to-zinc-900 p-5 sm:p-6 text-white shadow-lg">
        <div class="flex flex-col justify-between gap-5 sm:gap-6 lg:flex-row lg:items-center">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-300">
                    <span>🤖 LifeOS Command Center & Automation Hub</span>
                    <span>•</span>
                    <span>{{ $summary['date_formatted'] }}</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl lg:text-3xl">
                    Good Morning, {{ auth()->user()->name }}! ☀️
                </h1>
                <p class="max-w-2xl text-xs sm:text-sm text-zinc-300">
                    The automated Morning Briefing system is scheduled daily at <span class="font-semibold text-amber-300">{{ config('lifeos.schedules.briefing', '07:30') }} ({{ config('lifeos.timezone', 'Asia/Kuala_Lumpur') }})</span>. You can also trigger an immediate test briefing to Telegram at any time.
                </p>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 pt-1 text-xs">
                    @if ($summary['telegram_configured'])
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-1 font-medium text-emerald-300 border border-emerald-500/30">
                            <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Telegram Bot Connected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-2.5 py-1 font-medium text-amber-300 border border-amber-500/30">
                            <span class="size-2 rounded-full bg-amber-400"></span>
                            Simulation Mode (Set TELEGRAM_BOT_TOKEN in .env for Live API)
                        </span>
                    @endif
                    <span class="text-zinc-400">Cron: <code>lifeos:send-briefing</code></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <button
                    type="button"
                    wire:click="sendTestBriefing"
                    wire:loading.attr="disabled"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-xs sm:text-sm font-bold text-zinc-950 shadow-md shadow-amber-500/10 transition hover:bg-amber-300 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="sendTestBriefing">⚡ Dispatch Test Briefing to Telegram</span>
                    <span wire:loading wire:target="sendTestBriefing">⏳ Dispatching Briefing...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Cross-Module Daily Pulse KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('meetings.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-indigo-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Meetings Today</span>
                <span class="rounded-lg bg-indigo-500/10 p-2 text-indigo-600 dark:text-indigo-400">📅</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['meetings']->count() }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['meetings']->isNotEmpty() ? 'Next: '.$summary['meetings']->first()->start_time->format('h:i A') : 'No meetings scheduled today' }}
            </p>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-rose-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Top Urgent/High Tasks</span>
                <span class="rounded-lg bg-rose-500/10 p-2 text-rose-600 dark:text-rose-400">🔥</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['top_tasks']->count() }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['unlogged_habits']->count() }} habits pending check-in today
            </p>
        </a>

        <a href="{{ route('money.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-emerald-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Net Balance</span>
                <span class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">💰</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">RM {{ number_format($summary['total_wallet_balance'], 2) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                Yesterday's spend: RM {{ number_format($summary['yesterday_expense'], 2) }}
            </p>
        </a>

        <a href="{{ route('subscriptions.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Goals & Commitments</span>
                <span class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400">🎯</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['active_goals_count'] }} Active Goals</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['upcoming_subscriptions_count'] }} upcoming subscription bills
            </p>
        </a>
    </div>

    {{-- Main Two-Column Section: Live Telegram Briefing Preview & Scheduler Hub --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Left Column (7 cols): Live Morning Briefing Breakdown + Telegram Preview --}}
        <div class="flex flex-col gap-6 lg:col-span-7">
            {{-- Live Telegram Message Preview Bubble --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">📲 Live Telegram Message Preview</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Real HTML format dispatched by <code>BriefingService::generateForUser()</code> (kept in Malay as requested)</p>
                    </div>
                    <span class="rounded-full bg-sky-500/10 px-3 py-1 text-xs font-semibold text-sky-600 dark:text-sky-400">
                        Telegram Bot API (HTML)
                    </span>
                </div>

                <div class="mt-4 rounded-xl border border-zinc-800 bg-zinc-950 p-4 font-mono text-xs leading-relaxed text-zinc-100 shadow-inner">
                    <div class="whitespace-pre-line">{!! $this->briefingPreview !!}</div>
                </div>
            </div>

            {{-- Today's Focus Breakdown Cards --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                {{-- Top 3 Tasks Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">🔥 Top 3 Priority Tasks</h3>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Open Tasks →</a>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($summary['top_tasks'] as $task)
                            <div class="flex items-start justify-between gap-2 rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div>
                                    <p class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $task->title }}</p>
                                    @if ($task->due_date)
                                        <p class="mt-0.5 text-[11px] text-zinc-500">Due date: {{ $task->due_date->format('d M Y') }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $task->priority === 'urgent' ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                    {{ $task->priority }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-zinc-500">No urgent pending tasks.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Unlogged Habits Today Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">⚡ Pending Habit Check-ins</h3>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Log Now →</a>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($summary['unlogged_habits'] as $habit)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <span class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $habit->name }}</span>
                                <span class="rounded-full bg-orange-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-orange-600 dark:text-orange-400">
                                    🔥 {{ $habit->currentStreak() }} days
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-emerald-600 dark:text-emerald-400">All habits logged for today!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Scheduled Commands Runner & Automation Logs --}}
        <div class="flex flex-col gap-6 lg:col-span-5">
            {{-- Scheduler Control Panel --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-bold text-zinc-900 dark:text-white">⏱ Automation Schedule (routes/console.php)</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Scheduled Artisan Commands evaluated in <code>Asia/Kuala_Lumpur</code> timezone.</p>

                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @php
                        $briefingTime = config('lifeos.schedules.briefing', '07:30');
                        $goalsTime = config('lifeos.schedules.goals', '08:00');
                        $subsTime = config('lifeos.schedules.subscriptions', '09:00');
                        $budgetTime = config('lifeos.schedules.budgets', '20:00');
                        $tasksTime = config('lifeos.schedules.tasks', '21:00');
                        $reviewsTime = config('lifeos.schedules.reviews', '20:00');

                        $schedules = [
                            ['cmd' => 'meetings:send-reminders', 'time' => 'Every Minute', 'label' => 'Meeting Reminders'],
                            ['cmd' => 'lifeos:send-briefing', 'time' => "Daily • {$briefingTime}", 'label' => 'Morning Briefing Hub'],
                            ['cmd' => 'goals:send-reminders', 'time' => "Daily • {$goalsTime}", 'label' => 'Goal Progress Alerts'],
                            ['cmd' => 'subscriptions:check-due', 'time' => "Daily • {$subsTime}", 'label' => 'Subscription Renewal Due Alerts'],
                            ['cmd' => 'money:check-budgets', 'time' => "Daily • {$budgetTime}", 'label' => 'Monthly Budget Threshold Alerts'],
                            ['cmd' => 'tasks:send-reminders', 'time' => "Daily • {$tasksTime}", 'label' => 'Tasks & Habits Daily Recap'],
                            ['cmd' => 'reviews:send-weekly-reminder', 'time' => "Sunday • {$reviewsTime}", 'label' => 'Weekly Review Reflection Prompt'],
                        ];
                    @endphp

                    @foreach ($schedules as $item)
                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-zinc-900 dark:text-white">{{ $item['label'] }}</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    <code>{{ $item['cmd'] }}</code> • <span class="text-indigo-600 dark:text-indigo-400">{{ $item['time'] }}</span>
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="triggerArtisanCommand('{{ $item['cmd'] }}')"
                                wire:loading.attr="disabled"
                                class="shrink-0 cursor-pointer rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                            >
                                ▶ Run Now
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Automation Dispatch Logs --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">📜 Automation Dispatch Logs</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Last 12 automated and manual test dispatches.</p>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $logs->count() }} records
                    </span>
                </div>

                <div class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1">
                    @forelse ($logs as $log)
                        <div
                            wire:key="automation-log-{{ $log->id }}"
                            class="flex items-center justify-between gap-3 rounded-xl border p-3 transition {{ $selectedLog && $selectedLog->id === $log->id ? 'border-indigo-500/50 bg-indigo-500/5' : 'border-zinc-100 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-800/40' }}"
                        >
                            <button
                                type="button"
                                wire:click="selectLog({{ $log->id }})"
                                class="flex-1 cursor-pointer text-left"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $log->type }}</span>
                                    <span class="rounded-md px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $log->status === 'sent' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ($log->status === 'simulated' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-rose-500/15 text-rose-600 dark:text-rose-400') }}">
                                        {{ $log->status }}
                                    </span>
                                    <span class="rounded-md bg-zinc-200/70 px-1.5 py-0.5 text-[10px] text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        {{ $log->triggered_by }}
                                    </span>
                                </div>
                                <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $log->sent_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i:s A') }}
                                </p>
                            </button>

                            <button
                                type="button"
                                wire:click="confirmDeleteLog({{ $log->id }})"
                                class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-rose-500/10 hover:text-rose-500"
                                title="Delete log"
                            >
                                🗑️
                            </button>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-zinc-500">No automation logs recorded yet. Click the button above to test!</p>
                    @endforelse
                </div>

                @if ($logs->hasPages())
                    <div class="mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Flux Confirmation Delete Modal --}}
    <flux:modal wire:model.self="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Automation Log</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this dispatch log entry? This action cannot be undone.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteLog">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
