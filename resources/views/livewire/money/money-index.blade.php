<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                💰 Money & Budget Management
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Pantau baki setiap dompet, Net Worth, bajet bulanan, dan rekod transaksi pantas bawah 10 saat.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="openWalletModal"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                + Dompet
            </button>

            <button
                type="button"
                wire:click="openBudgetModal"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                🎯 Set Bajet
            </button>

            <button
                type="button"
                wire:click="openQuickTransactionModal('expense')"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Quick Transaction
            </button>
        </div>
    </div>

    {{-- Net Worth & Wallets Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Net Worth Card --}}
        <div class="flex flex-col justify-between rounded-2xl bg-linear-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-5 text-white shadow-sm dark:from-zinc-100 dark:via-white dark:to-zinc-200 dark:text-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Jumlah Net Worth
                </span>
                <span class="rounded-lg bg-white/10 px-2 py-0.5 text-xs font-medium dark:bg-zinc-900/10">
                    MYR
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-extrabold tracking-tight">
                    RM {{ number_format($this->netWorth, 2) }}
                </p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-600">
                    Gabungan {{ $this->wallets->count() }} dompet aktif
                </p>
            </div>
        </div>

        {{-- Individual Wallet Cards --}}
        @foreach ($this->wallets as $wallet)
            @php
                $balance = $wallet->currentBalance();
                $walletIcon = match ($wallet->type) {
                    'bank' => '🏦',
                    'ewallet' => '📱',
                    default => '💵',
                };
                $walletTypeLabel = match ($wallet->type) {
                    'bank' => 'Bank Account',
                    'ewallet' => 'E-Wallet',
                    default => 'Tunai (Cash)',
                };
            @endphp

            <div
                wire:key="wallet-card-{{ $wallet->id }}"
                class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-zinc-300 dark:border-zinc-700/80 dark:bg-zinc-900"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="flex size-8 items-center justify-center rounded-xl bg-zinc-100 text-base dark:bg-zinc-800">
                            {{ $walletIcon }}
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $wallet->name }}</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $walletTypeLabel }}</p>
                        </div>
                    </div>
                    <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        {{ $wallet->currency }}
                    </span>
                </div>

                <div class="mt-4">
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        RM {{ number_format($balance, 2) }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-zinc-400 dark:text-zinc-500">
                        Baki permulaan: RM {{ number_format((float) $wallet->starting_balance, 2) }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Monthly Budgets Section (Green / Yellow / Red Progress Bar) --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    📊 Bajet Bulanan ({{ $selectedMonthYear }})
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Hijau (&lt; 75%), Kuning (75% - 99%), Merah (100%+ Melebihi Bajet)
                </p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="month"
                    wire:model.live="selectedMonthYear"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-semibold text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                />
            </div>
        </div>

        @if ($this->budgets->isEmpty())
            <p class="mt-4 text-xs italic text-zinc-400 dark:text-zinc-500">
                Tiada bajet ditetapkan untuk bulan {{ $selectedMonthYear }}. Klik "Set Bajet" di atas untuk menetapkan had perbelanjaan.
            </p>
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach ($this->budgets as $budget)
                    @php
                        $spent = $budget->spentAmount();
                        $limit = (float) $budget->amount;
                        $percent = $budget->progressPercentage();

                        // Hijau (<75%), Kuning (75-99.9%), Merah (>=100%)
                        $barColor = match (true) {
                            $percent >= 100.0 => 'bg-rose-500',
                            $percent >= 75.0 => 'bg-amber-500',
                            default => 'bg-emerald-500',
                        };

                        $badgeColor = match (true) {
                            $percent >= 100.0 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                            $percent >= 75.0 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                            default => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        };
                    @endphp

                    <div
                        wire:key="budget-card-{{ $budget->id }}"
                        class="rounded-xl border border-zinc-100 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">{{ $budget->category->icon ?? '🗂' }}</span>
                                <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $budget->category->name }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $badgeColor }}">
                                    {{ number_format($percent, 1) }}%
                                </span>
                                <button
                                    type="button"
                                    wire:click="sendBudgetAlert({{ $budget->id }})"
                                    title="Hantar Amaran Bajet ke Telegram"
                                    class="cursor-pointer rounded-lg p-1 text-zinc-400 hover:bg-zinc-200/60 hover:text-sky-600 dark:hover:bg-zinc-700"
                                >
                                    🔔
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300">
                            <span>Belanja: <b>RM {{ number_format($spent, 2) }}</b></span>
                            <span>Had: <b>RM {{ number_format($limit, 2) }}</b></span>
                        </div>

                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $barColor }}"
                                style="width: {{ min(100, max(0, $percent)) }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Transactions Table --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    🧾 Transaksi Terkini
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Senarai aliran keluar masuk dan pindahan antara dompet.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select
                    wire:model.live="filterType"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">Semua Jenis</option>
                    <option value="expense">Perbelanjaan (Expense)</option>
                    <option value="income">Pendapatan (Income)</option>
                    <option value="transfer">Pindahan (Transfer)</option>
                </select>

                <select
                    wire:model.live="filterWalletId"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">Semua Dompet</option>
                    @foreach ($this->wallets as $wallet)
                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-y border-zinc-100 bg-zinc-50/60 uppercase text-zinc-400 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-500">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold">Tarikh</th>
                        <th class="px-3 py-2.5 font-semibold">Kategori / Jenis</th>
                        <th class="px-3 py-2.5 font-semibold">Dompet</th>
                        <th class="px-3 py-2.5 font-semibold">Catatan</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Jumlah</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->transactions as $transaction)
                        <tr wire:key="tx-row-{{ $transaction->id }}" class="transition hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                            <td class="whitespace-nowrap px-3 py-3 font-medium text-zinc-600 dark:text-zinc-300">
                                {{ $transaction->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-3 py-3">
                                @if ($transaction->type === 'transfer')
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-sky-600 dark:text-sky-400">
                                        <span>🔄</span> Pindahan Dompet
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-zinc-800 dark:text-zinc-200">
                                        <span>{{ $transaction->category?->icon ?? '🏷' }}</span>
                                        {{ $transaction->category?->name ?? ucfirst($transaction->type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $transaction->wallet->name }}
                                @if ($transaction->type === 'transfer' && $transaction->targetWallet)
                                    <span class="text-zinc-400">→</span> {{ $transaction->targetWallet->name }}
                                @endif
                            </td>
                            <td class="px-3 py-3 text-zinc-500 dark:text-zinc-400">
                                {{ $transaction->notes ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-right font-bold">
                                @if ($transaction->type === 'expense')
                                    <span class="text-rose-600 dark:text-rose-400">
                                        - RM {{ number_format((float) $transaction->amount, 2) }}
                                    </span>
                                @elseif ($transaction->type === 'income')
                                    <span class="text-emerald-600 dark:text-emerald-400">
                                        + RM {{ number_format((float) $transaction->amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-sky-600 dark:text-sky-400">
                                        ↔ RM {{ number_format((float) $transaction->amount, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="deleteTransaction({{ $transaction->id }})"
                                    wire:confirm="Padam transaksi ini?"
                                    class="cursor-pointer rounded-lg p-1 text-zinc-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40"
                                    title="Padam Transaksi"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs italic text-zinc-400">
                                Tiada rekod transaksi ditemui.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Transaction Modal (< 10 Seconds) --}}
    @if ($showTransactionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        ⚡ Quick Transaction
                    </h3>
                    <button
                        type="button"
                        wire:click="closeTransactionModal"
                        class="cursor-pointer rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        ✕
                    </button>
                </div>

                <form wire:submit="saveTransaction" class="mt-4 space-y-4">
                    {{-- One-Click Type Switcher --}}
                    <div class="grid grid-cols-3 gap-1.5 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                        <button
                            type="button"
                            wire:click="setQuickType('expense')"
                            class="cursor-pointer rounded-lg py-2 text-xs font-bold transition {{ $transactionType === 'expense' ? 'bg-rose-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300' }}"
                        >
                            Perbelanjaan
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickType('income')"
                            class="cursor-pointer rounded-lg py-2 text-xs font-bold transition {{ $transactionType === 'income' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300' }}"
                        >
                            Pendapatan
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickType('transfer')"
                            class="cursor-pointer rounded-lg py-2 text-xs font-bold transition {{ $transactionType === 'transfer' ? 'bg-sky-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300' }}"
                        >
                            Pindahan
                        </button>
                    </div>

                    {{-- Amount + Quick Presets --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Jumlah (RM) *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            wire:model="transactionAmount"
                            placeholder="0.00"
                            autofocus
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-lg font-bold text-zinc-900 focus:border-zinc-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('transactionAmount') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror

                        <div class="mt-2 flex items-center gap-1.5">
                            @foreach ([10, 20, 50, 100] as $preset)
                                <button
                                    type="button"
                                    wire:click="setPresetAmount({{ $preset }})"
                                    class="cursor-pointer rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                                >
                                    RM {{ $preset }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                {{ $transactionType === 'transfer' ? 'Dari Dompet *' : 'Dompet *' }}
                            </label>
                            <select
                                wire:model="transactionWalletId"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                @foreach ($this->wallets as $wallet)
                                    <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($transactionType === 'transfer')
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Ke Dompet Sasaran *</label>
                                <select
                                    wire:model="transactionTargetWalletId"
                                    class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    @foreach ($this->wallets as $wallet)
                                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                    @endforeach
                                </select>
                                @error('transactionTargetWalletId') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Kategori *</label>
                                <select
                                    wire:model="transactionCategoryId"
                                    class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    @foreach ($this->categories->where('type', $transactionType) as $category)
                                        <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('transactionCategoryId') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tarikh *</label>
                            <input
                                type="date"
                                wire:model="transactionDate"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Catatan Ringkas</label>
                            <input
                                type="text"
                                wire:model="transactionNotes"
                                placeholder="Contoh: Makan tengah hari"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeTransactionModal"
                            class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl bg-zinc-900 px-5 py-2 text-xs font-bold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900"
                        >
                            Simpan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Add Wallet --}}
    @if ($showWalletModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Tambah Dompet Baharu</h3>
                    <button type="button" wire:click="$set('showWalletModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveWallet" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Nama Dompet *</label>
                        <input
                            type="text"
                            wire:model="walletName"
                            placeholder="Contoh: Maybank MAE / Touch 'n Go"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('walletName') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Jenis</label>
                            <select
                                wire:model="walletType"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="bank">Bank</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="cash">Tunai (Cash)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Baki Permulaan (RM)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="walletStartingBalance"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showWalletModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Batal</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">Simpan Dompet</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Set Monthly Budget --}}
    @if ($showBudgetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Set Bajet Bulanan ({{ $selectedMonthYear }})</h3>
                    <button type="button" wire:click="$set('showBudgetModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveBudget" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Kategori Perbelanjaan *</label>
                        <select
                            wire:model="budgetCategoryId"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            @foreach ($this->categories->where('type', 'expense') as $category)
                                <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Had Bajet Bulanan (RM) *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="1"
                            wire:model="budgetAmount"
                            placeholder="800.00"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('budgetAmount') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showBudgetModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Batal</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">Simpan Bajet</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

