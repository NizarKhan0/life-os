@php
    $weekStart = \Illuminate\Support\Carbon::parse($weekStartDate);
    $weekEnd = $weekStart->copy()->endOfWeek();
    $snap = $this->snapshot;
    $net = $snap['total_income'] - $snap['total_expense'];
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                🧠 Weekly Review & Brain Dump
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Kosongkan fikiran, semak metrik minggu ini, dan refleksi untuk minggu yang lebih baik.
            </p>
        </div>

        <button
            type="button"
            wire:click="sendTelegramReminder"
            class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300"
        >
            🔔 Hantar Snapshot ke Telegram
        </button>
    </div>

    {{-- Brain Dump Quick Bar --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">🗒 Brain Dump</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Taip apa sahaja yang terlintas dan tekan Enter — terus disimpan.</p>
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                <input type="checkbox" wire:model.live="showProcessedDumps" class="size-4 rounded border-zinc-300" />
                Tunjuk yang telah diproses
            </label>
        </div>

        <form wire:submit="saveDump">
            <input
                type="text"
                wire:model="dumpContent"
                placeholder="💭 Idea, kebimbangan, perkara nak buat... tekan Enter ↵"
                class="w-full rounded-xl border border-zinc-300 bg-zinc-50/70 px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:bg-white focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800/80 dark:text-zinc-100 dark:focus:bg-zinc-800"
            />
            @error('dumpContent') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
        </form>

        <ul class="mt-4 flex flex-col gap-2">
            @forelse ($this->brainDumps as $dump)
                <li
                    wire:key="dump-{{ $dump->id }}"
                    class="group flex items-start justify-between gap-3 rounded-xl border px-3.5 py-2.5 transition {{ $dump->is_processed ? 'border-zinc-100 bg-zinc-50/50 dark:border-zinc-800 dark:bg-zinc-800/20' : 'border-amber-200/70 bg-amber-50/40 dark:border-amber-800/40 dark:bg-amber-950/10' }}"
                >
                    <label class="flex flex-1 cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            wire:click="toggleDumpProcessed({{ $dump->id }})"
                            @checked($dump->is_processed)
                            class="mt-0.5 size-4 cursor-pointer rounded border-zinc-300 text-emerald-600"
                        />
                        <div class="flex flex-col">
                            <span class="text-sm {{ $dump->is_processed ? 'text-zinc-400 line-through dark:text-zinc-500' : 'text-zinc-800 dark:text-zinc-200' }}">
                                {{ $dump->content }}
                            </span>
                            <span class="text-[11px] text-zinc-400">{{ $dump->created_at?->diffForHumans() }}</span>
                        </div>
                    </label>
                    <button
                        type="button"
                        wire:click="deleteDump({{ $dump->id }})"
                        class="invisible cursor-pointer rounded-lg p-1 text-zinc-400 hover:text-rose-600 group-hover:visible"
                        title="Padam Nota"
                    >
                        🗑️
                    </button>
                </li>
            @empty
                <li class="py-4 text-center text-xs italic text-zinc-400">Fikiran anda kosong & tenang. Tiada nota belum diproses. ✨</li>
            @endforelse
        </ul>
    </div>

    {{-- Week Navigator --}}
    <div class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700/80 dark:bg-zinc-900">
        <button type="button" wire:click="previousWeek" class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
            ← Minggu Lepas
        </button>
        <div class="text-center">
            <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                {{ $weekStart->format('d M') }} — {{ $weekEnd->format('d M Y') }}
            </p>
            @if ($this->isCurrentWeek)
                <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Minggu Semasa</span>
            @endif
        </div>
        <button
            type="button"
            wire:click="nextWeek"
            @disabled($this->isCurrentWeek)
            class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-300 dark:hover:bg-zinc-800"
        >
            Minggu Depan →
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Weekly Snapshot Card --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-linear-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-5 text-white shadow-sm dark:from-zinc-100 dark:via-white dark:to-zinc-200 dark:text-zinc-900">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">📊 Weekly Snapshot</span>
                <p class="mt-1 text-[11px] text-zinc-400 dark:text-zinc-500">Metrik dikira automatik daripada modul lain</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-white/10 p-3 dark:bg-zinc-900/5">
                    <p class="text-2xl font-extrabold">{{ $snap['tasks_completed'] }}</p>
                    <p class="text-[11px] text-zinc-300 dark:text-zinc-600">✅ Task siap</p>
                </div>
                <div class="rounded-xl bg-white/10 p-3 dark:bg-zinc-900/5">
                    <p class="text-2xl font-extrabold">{{ $snap['tasks_pending'] }}</p>
                    <p class="text-[11px] text-zinc-300 dark:text-zinc-600">⏳ Tertunggak</p>
                </div>
                <div class="rounded-xl bg-white/10 p-3 dark:bg-zinc-900/5">
                    <p class="text-2xl font-extrabold">{{ $snap['habit_checkins'] }}</p>
                    <p class="text-[11px] text-zinc-300 dark:text-zinc-600">🔥 Check-in habit</p>
                </div>
                <div class="rounded-xl bg-white/10 p-3 dark:bg-zinc-900/5">
                    <p class="text-2xl font-extrabold">{{ $snap['meetings_held'] }}</p>
                    <p class="text-[11px] text-zinc-300 dark:text-zinc-600">📅 Mesyuarat</p>
                </div>
            </div>

            <div class="space-y-2 border-t border-white/10 pt-3 text-xs dark:border-zinc-900/10">
                <div class="flex justify-between">
                    <span class="text-zinc-300 dark:text-zinc-600">💸 Belanja minggu ini</span>
                    <span class="font-bold text-rose-300 dark:text-rose-600">RM {{ number_format($snap['total_expense'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-300 dark:text-zinc-600">💰 Pendapatan</span>
                    <span class="font-bold text-emerald-300 dark:text-emerald-600">RM {{ number_format($snap['total_income'], 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-white/10 pt-2 dark:border-zinc-900/10">
                    <span class="font-semibold">Aliran bersih</span>
                    <span class="font-extrabold {{ $net >= 0 ? 'text-emerald-300 dark:text-emerald-600' : 'text-rose-300 dark:text-rose-600' }}">
                        {{ $net >= 0 ? '+' : '-' }} RM {{ number_format(abs($net), 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Reflection Form (3 Questions) --}}
        <form wire:submit="saveReview" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs lg:col-span-2 dark:border-zinc-700/80 dark:bg-zinc-900">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">✍️ Refleksi Mingguan</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Jawab 3 soalan ini dengan jujur. Snapshot metrik akan disimpan bersama.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-400">🏆 1. Apakah kemenangan terbesar minggu ini?</label>
                <textarea
                    wire:model="wins"
                    rows="3"
                    placeholder="Pencapaian, perkara yang berjalan lancar, momen yang membanggakan..."
                    class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-emerald-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-amber-700 dark:text-amber-400">⚡ 2. Apakah cabaran atau halangan yang dihadapi?</label>
                <textarea
                    wire:model="challenges"
                    rows="3"
                    placeholder="Apa yang menyekat, membazir masa, atau tidak berjalan seperti dirancang..."
                    class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-sky-700 dark:text-sky-400">🚀 3. Apa yang akan diperbaiki minggu depan?</label>
                <textarea
                    wire:model="improvements"
                    rows="3"
                    placeholder="Satu atau dua tindakan konkrit untuk minggu hadapan..."
                    class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-sky-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                ></textarea>
            </div>

            <div class="flex justify-end border-t border-zinc-100 pt-4 dark:border-zinc-800">
                <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-5 py-2.5 text-xs font-bold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                    Simpan Refleksi
                </button>
            </div>
        </form>
    </div>

    {{-- Past Reviews --}}
    @if ($this->pastReviews->isNotEmpty())
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <h2 class="mb-3 text-base font-bold text-zinc-900 dark:text-zinc-100">📚 Review Terdahulu</h2>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->pastReviews as $review)
                    <button
                        type="button"
                        wire:key="past-review-{{ $review->id }}"
                        wire:click="openReview('{{ $review->week_start_date->toDateString() }}')"
                        class="cursor-pointer rounded-xl border border-zinc-100 bg-zinc-50/70 p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-800/40 dark:hover:border-zinc-600"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $review->week_start_date->format('d M') }} — {{ $review->weekEndDate()->format('d M Y') }}
                            </span>
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-bold {{ $review->isComplete() ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300' }}">
                                {{ $review->isComplete() ? 'Lengkap' : 'Separa' }}
                            </span>
                        </div>
                        @if ($review->wins)
                            <p class="mt-2 line-clamp-2 text-xs text-zinc-500 dark:text-zinc-400">🏆 {{ $review->wins }}</p>
                        @endif
                        @if ($review->metrics_snapshot)
                            <p class="mt-2 text-[11px] text-zinc-400">
                                ✅ {{ $review->metrics_snapshot['tasks_completed'] ?? 0 }} task ·
                                💸 RM {{ number_format((float) ($review->metrics_snapshot['total_expense'] ?? 0), 2) }}
                            </p>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>

