<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-6">
    @php
        $summary = $this->summary;
        $logs = $this->logs;
        $selectedLog = $this->selectedLogId
            ? $logs->firstWhere('id', $this->selectedLogId)
            : $logs->first();
    @endphp

    {{-- Top Hero Banner: Morning Briefing & Manual Trigger --}}
    <div class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/90 via-zinc-900 to-zinc-900 p-6 text-white shadow-lg">
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-300">
                    <span>🤖 LifeOS Command Center & Automation Hub</span>
                    <span>•</span>
                    <span>{{ $summary['date_formatted'] }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    Selamat Pagi, {{ auth()->user()->name }}! ☀️
                </h1>
                <p class="max-w-2xl text-sm text-zinc-300">
                    Sistem automasi Morning Briefing dijadualkan setiap hari pada jam <span class="font-semibold text-amber-300">07:30 AM (Asia/Kuala_Lumpur)</span>. Anda juga boleh menghantar ujian manual ke Telegram pada bila-bila masa.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-1 text-xs">
                    @if ($summary['telegram_configured'])
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-1 font-medium text-emerald-300 border border-emerald-500/30">
                            <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Telegram Bot Tersambung
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/20 px-2.5 py-1 font-medium text-amber-300 border border-amber-500/30">
                            <span class="size-2 rounded-full bg-amber-400"></span>
                            Mod Simulasi Tempatan (Tetapkan TELEGRAM_BOT_TOKEN dalam .env untuk Live API)
                        </span>
                    @endif
                    <span class="text-zinc-400">Cron: <code>lifeos:send-briefing</code></span>
                </div>
            </div>

            <div class="flex flex-col items-stretch gap-2.5 sm:flex-row lg:items-center">
                <button
                    type="button"
                    wire:click="sendTestBriefing"
                    wire:loading.attr="disabled"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-bold text-zinc-950 shadow-md shadow-amber-500/10 transition hover:bg-amber-300 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="sendTestBriefing">⚡ Hantar Ujian Briefing ke Telegram</span>
                    <span wire:loading wire:target="sendTestBriefing">⏳ Sedang Menghantar Briefing...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Cross-Module Daily Pulse KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('meetings.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-indigo-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Mesyuarat Hari Ini</span>
                <span class="rounded-lg bg-indigo-500/10 p-2 text-indigo-600 dark:text-indigo-400">📅</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['meetings']->count() }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['meetings']->isNotEmpty() ? 'Seterusnya: '.$summary['meetings']->first()->start_time->format('h:i A') : 'Tiada mesyuarat hari ini' }}
            </p>
        </a>

        <a href="{{ route('tasks.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-rose-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Top Urgent/High Tasks</span>
                <span class="rounded-lg bg-rose-500/10 p-2 text-rose-600 dark:text-rose-400">🔥</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['top_tasks']->count() }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['unlogged_habits']->count() }} tabiat belum dilog hari ini
            </p>
        </a>

        <a href="{{ route('money.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-emerald-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Jumlah Baki Dompet</span>
                <span class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">💰</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">RM {{ number_format($summary['total_wallet_balance'], 2) }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                Belanja semalam: RM {{ number_format($summary['yesterday_expense'], 2) }}
            </p>
        </a>

        <a href="{{ route('subscriptions.index') }}" wire:navigate class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-amber-400 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Matlamat & Komitmen</span>
                <span class="rounded-lg bg-amber-500/10 p-2 text-amber-600 dark:text-amber-400">🎯</span>
            </div>
            <p class="mt-3 text-2xl font-bold text-zinc-900 dark:text-white">{{ $summary['active_goals_count'] }} Matlamat Aktif</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $summary['upcoming_subscriptions_count'] }} bil langganan hampir tarikh
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
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">📲 Pratonton Langsung Mesej Telegram</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Format HTML sebenar yang dijana oleh <code>BriefingService::generateForUser()</code></p>
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
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">🔥 Top 3 Tugasan Utama</h3>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Buka Tasks →</a>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($summary['top_tasks'] as $task)
                            <div class="flex items-start justify-between gap-2 rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div>
                                    <p class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $task->title }}</p>
                                    @if ($task->due_date)
                                        <p class="mt-0.5 text-[11px] text-zinc-500">Tarikh akhir: {{ $task->due_date->format('d M Y') }}</p>
                                    @endif
                                </div>
                                <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $task->priority === 'urgent' ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                    {{ $task->priority }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-zinc-500">Tiada tugasan kritikal yang tertunggak.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Unlogged Habits Today Card --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white">⚡ Tabiat Belum Dilog Hari Ini</h3>
                        <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Log Sekarang →</a>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($summary['unlogged_habits'] as $habit)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-100 bg-zinc-50/70 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <span class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $habit->name }}</span>
                                <span class="rounded-full bg-orange-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-orange-600 dark:text-orange-400">
                                    🔥 {{ $habit->currentStreak() }} hari
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-emerald-600 dark:text-emerald-400">Syabas! Semua tabiat hari ini selesai dilog.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (5 cols): Scheduled Commands Runner & Automation Logs --}}
        <div class="flex flex-col gap-6 lg:col-span-5">
            {{-- Scheduler Control Panel --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-bold text-zinc-900 dark:text-white">⏱ Jadual Automasi (routes/console.php)</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Senarai penuh Artisan Command berjadual mengikut zon masa <code>Asia/Kuala_Lumpur</code>.</p>

                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @php
                        $schedules = [
                            ['cmd' => 'meetings:send-reminders', 'time' => 'Setiap Minit', 'label' => 'Peringatan Mesyuarat'],
                            ['cmd' => 'lifeos:send-briefing', 'time' => 'Harian • 07:30 AM', 'label' => 'Morning Briefing Hub'],
                            ['cmd' => 'goals:send-reminders', 'time' => 'Harian • 08:00 AM', 'label' => 'Peringatan Matlamat'],
                            ['cmd' => 'subscriptions:check-due', 'time' => 'Harian • 09:00 AM', 'label' => 'Semakan Bil Langganan'],
                            ['cmd' => 'money:check-budgets', 'time' => 'Harian • 08:00 PM', 'label' => 'Amaran Bajet Bulanan'],
                            ['cmd' => 'tasks:send-reminders', 'time' => 'Harian • 09:00 PM', 'label' => 'Peringatan Tasks & Habits'],
                            ['cmd' => 'reviews:send-weekly-reminder', 'time' => 'Ahad • 08:00 PM', 'label' => 'Refleksi Weekly Review'],
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
                                ▶ Jalankan
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Automation Dispatch Logs --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">📜 Log Penghantaran Automasi</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Rekod 12 penghantaran briefing dan peringatan terkini.</p>
                    </div>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $logs->count() }} rekod
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
                                wire:click="deleteLog({{ $log->id }})"
                                class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-rose-500/10 hover:text-rose-500"
                                title="Padam log"
                            >
                                🗑️
                            </button>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-zinc-500">Tiada rekod automasi lagi. Klik butang ujian di atas untuk mula!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

