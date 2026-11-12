<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                📅 Meetings & Time Management
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Urus jadual mesyuarat dan peringatan automatik Telegram Bot API setiap minit.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="testTelegramAlert(null)"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-300 dark:hover:bg-sky-900/50"
            >
                <span>🔔</span>
                Test Telegram Alert
            </button>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Meeting
            </button>
        </div>
    </div>

    {{-- Tabs: Upcoming & Past Meetings --}}
    <div class="flex items-center gap-2 border-b border-zinc-200 pb-3 dark:border-zinc-700/80">
        <button
            type="button"
            wire:click="setTab('upcoming')"
            class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition {{ $activeTab === 'upcoming' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
        >
            <span> akan datang (Upcoming)</span>
            <span class="rounded-full px-2 py-0.5 text-[11px] {{ $activeTab === 'upcoming' ? 'bg-white/20 text-white dark:bg-zinc-900/20 dark:text-zinc-900' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                {{ $this->upcomingCount }}
            </span>
        </button>

        <button
            type="button"
            wire:click="setTab('past')"
            class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition {{ $activeTab === 'past' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
        >
            <span>Lepas (Past Meetings)</span>
            <span class="rounded-full px-2 py-0.5 text-[11px] {{ $activeTab === 'past' ? 'bg-white/20 text-white dark:bg-zinc-900/20 dark:text-zinc-900' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                {{ $this->pastCount }}
            </span>
        </button>
    </div>

    {{-- Meetings List / Grid --}}
    @if ($this->meetings->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/50 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900/30">
            <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-zinc-200/70 text-xl dark:bg-zinc-800">
                📅
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                Tiada mesyuarat {{ $activeTab === 'upcoming' ? 'akan datang' : 'lepas' }}
            </h3>
            <p class="mt-1 max-w-sm text-xs text-zinc-500 dark:text-zinc-400">
                Jadualkan mesyuarat baharu dan terima peringatan automatik di Telegram sebelum ia bermula.
            </p>
            <button
                type="button"
                wire:click="openCreateModal"
                class="mt-4 cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                + Add Meeting
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($this->meetings as $meeting)
                @php
                    $statusStyles = match ($meeting->status) {
                        'completed' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
                        'cancelled' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300',
                        default => 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300',
                    };
                @endphp

                <div
                    wire:key="meeting-card-{{ $meeting->id }}"
                    class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-zinc-300 dark:border-zinc-700/80 dark:bg-zinc-900 dark:hover:border-zinc-600"
                >
                    <div>
                        {{-- Badges & Quick Actions --}}
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-[11px] font-semibold {{ $statusStyles }}">
                                    {{ ucfirst($meeting->status) }}
                                </span>

                                {{-- Reminder Status Badges --}}
                                @foreach ($meeting->reminders as $reminder)
                                    @if ($reminder->is_sent)
                                        <span
                                            title="Dihantar pada {{ $reminder->sent_at?->format('d/m/Y h:i A') }}"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-500/20 dark:text-emerald-300"
                                        >
                                            🔔 Sent ({{ $reminder->remind_before_minutes }}m)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-500/20 dark:text-amber-300">
                                            ⏳ Pending ({{ $reminder->remind_before_minutes }}m before)
                                        </span>
                                    @endif
                                @endforeach
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="testTelegramAlert({{ $meeting->id }})"
                                    title="Test Telegram Alert untuk mesyuarat ini"
                                    class="inline-flex cursor-pointer items-center gap-1 rounded-lg border border-sky-200 bg-sky-50/70 px-2 py-1 text-[11px] font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-950/50 dark:text-sky-300"
                                >
                                    🔔 Test Alert
                                </button>
                                <button
                                    type="button"
                                    wire:click="editMeeting({{ $meeting->id }})"
                                    title="Edit Mesyuarat"
                                    class="cursor-pointer rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteMeeting({{ $meeting->id }})"
                                    wire:confirm="Adakah anda pasti mahu memadam mesyuarat ini?"
                                    title="Padam Mesyuarat"
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
                            {{ $meeting->title }}
                        </h2>

                        @if ($meeting->description)
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $meeting->description }}
                            </p>
                        @endif
                    </div>

                    {{-- Time & Location Footer --}}
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-zinc-100 pt-3.5 text-xs dark:border-zinc-800">
                        <div class="flex flex-col gap-1 text-zinc-600 dark:text-zinc-300">
                            <span class="font-medium">
                                🕒 {{ $meeting->start_time->format('d M Y, h:i A') }}
                                @if ($meeting->end_time)
                                    — {{ $meeting->end_time->format('h:i A') }}
                                @endif
                            </span>

                            @if ($meeting->location_or_link)
                                <span class="text-zinc-500 dark:text-zinc-400">
                                    📍
                                    @if (str_starts_with($meeting->location_or_link, 'http'))
                                        <a href="{{ $meeting->location_or_link }}" target="_blank" rel="noopener noreferrer" class="text-sky-600 underline hover:text-sky-500 dark:text-sky-400">
                                            {{ $meeting->location_or_link }}
                                        </a>
                                    @else
                                        {{ $meeting->location_or_link }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        <span class="rounded-lg bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $meeting->start_time->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Add / Edit Meeting --}}
    @if ($showMeetingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $editingMeetingId ? 'Kemaskini Mesyuarat' : 'Jadualkan Mesyuarat Baharu' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="closeMeetingModal"
                        class="cursor-pointer rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800"
                    >
                        ✕
                    </button>
                </div>

                <form wire:submit="saveMeeting" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tajuk Mesyuarat *</label>
                        <input
                            type="text"
                            wire:model="title"
                            placeholder="Contoh: Sprint Planning Suku Keempat"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('title') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Lokasi atau Pautan (Google Meet / Zoom / Bilik)</label>
                        <input
                            type="text"
                            wire:model="location_or_link"
                            placeholder="https://meet.google.com/... atau Bilik Mesyuarat Utama"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('location_or_link') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Masa Mula *</label>
                            <input
                                type="datetime-local"
                                wire:model="start_time"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('start_time') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Masa Tamat (Pilihan)</label>
                            <input
                                type="datetime-local"
                                wire:model="end_time"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('end_time') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Peringatan Telegram Sebelum</label>
                            <select
                                wire:model="remind_before_minutes"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="5">5 minit sebelum</option>
                                <option value="10">10 minit sebelum</option>
                                <option value="15">15 minit sebelum (Lalai)</option>
                                <option value="30">30 minit sebelum</option>
                                <option value="60">1 jam sebelum</option>
                                <option value="120">2 jam sebelum</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Status</label>
                            <select
                                wire:model="status"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="scheduled">Dijadualkan (Scheduled)</option>
                                <option value="completed">Selesai (Completed)</option>
                                <option value="cancelled">Dibatalkan (Cancelled)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Agenda / Catatan</label>
                        <textarea
                            wire:model="description"
                            rows="2"
                            placeholder="Senarai agenda ringkas mesyuarat..."
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeMeetingModal"
                            class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            {{ $editingMeetingId ? 'Simpan Perubahan' : 'Simpan Mesyuarat' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

