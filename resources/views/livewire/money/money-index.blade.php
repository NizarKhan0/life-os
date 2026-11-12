<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                💰 Money & Budget Management
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Monitor multi-wallet balances, total liquid net worth, category budgets, and rapid transactions.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button
                type="button"
                wire:click="openWalletModal"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                + Add Wallet
            </button>

            <button
                type="button"
                wire:click="openCategoryModal('expense')"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                🏷️ + Add Category
            </button>

            <button
                type="button"
                wire:click="openBudgetModal"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                🎯 Set Budget
            </button>

            <button
                type="button"
                wire:click="openQuickTransactionModal('expense')"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                ⚡ Quick Transaction
            </button>
        </div>
    </div>

    {{-- Net Worth & Wallets Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Net Worth Card --}}
        <div class="flex flex-col justify-between rounded-2xl bg-linear-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-5 text-white shadow-sm dark:from-zinc-100 dark:via-white dark:to-zinc-200 dark:text-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Total Net Worth
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
                    Across {{ $this->wallets->count() }} active wallets
                </p>
            </div>
        </div>

        {{-- Individual Wallet Cards --}}
        @foreach ($this->wallets as $wallet)
            @php
                $balance = $wallet->currentBalance();
                $walletIcon = $wallet->displayIcon();
                $walletTypeLabel = match ($wallet->type) {
                    'bank' => 'Bank Account',
                    'ewallet' => 'E-Wallet',
                    default => 'Cash',
                };
            @endphp

            <div
                wire:key="wallet-card-{{ $wallet->id }}"
                class="group relative flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs transition hover:border-zinc-300 dark:border-zinc-700/80 dark:bg-zinc-900"
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
                    <div class="flex items-center gap-1">
                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ $wallet->currency }}
                        </span>
                        <button
                            type="button"
                            wire:click="editWallet({{ $wallet->id }})"
                            title="Edit wallet"
                            class="cursor-pointer rounded-md p-1 text-zinc-400 opacity-70 transition hover:bg-zinc-100 hover:text-zinc-700 hover:opacity-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        >
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            wire:click="confirmDeleteWallet({{ $wallet->id }})"
                            title="Delete wallet"
                            class="cursor-pointer rounded-md p-1 text-zinc-400 opacity-70 transition hover:bg-rose-50 hover:text-rose-600 hover:opacity-100 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                        >
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        RM {{ number_format($balance, 2) }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-zinc-400 dark:text-zinc-500">
                        Starting Balance: RM {{ number_format((float) $wallet->starting_balance, 2) }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Monthly Budgets Section (Green / Yellow / Red Progress Bar) --}}
    <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 sm:p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-sm">📊</span>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Monthly Budgets
                    </h2>
                    <span class="rounded-full bg-amber-500/10 px-2.5 py-0.5 font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
                        {{ \Illuminate\Support\Carbon::parse($selectedMonthYear . '-01')->translatedFormat('F Y') }}
                    </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Thresholds: <span class="text-emerald-500 font-semibold">Green (&lt; 75%)</span>, <span class="text-amber-500 font-semibold">Yellow (75% - 99%)</span>, <span class="text-rose-500 font-semibold">Red (&ge; 100% Alert)</span>
                </p>
            </div>

            {{-- Calendar Month Selector Pill with Generous Padding & Breathing Room --}}
            <div class="flex items-center gap-2.5 rounded-2xl border border-zinc-200/90 bg-zinc-50/90 px-3.5 py-2 shadow-2xs transition hover:border-zinc-300 dark:border-zinc-700/80 dark:bg-zinc-800/60 dark:hover:border-zinc-600 sm:self-center">
                <label for="month-picker" class="flex items-center gap-2 text-xs font-bold text-zinc-600 dark:text-zinc-300 cursor-pointer">
                    <svg class="size-4 text-amber-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">Select Month:</span>
                </label>
                <input
                    id="month-picker"
                    type="month"
                    wire:model.live="selectedMonthYear"
                    class="cursor-pointer rounded-xl border border-zinc-200 bg-white px-3.5 py-1.5 text-xs font-bold text-zinc-800 shadow-2xs transition hover:border-amber-400 focus:border-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:border-amber-500 [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:p-1 [&::-webkit-calendar-picker-indicator]:opacity-80 hover:[&::-webkit-calendar-picker-indicator]:opacity-100"
                />
            </div>
        </div>

        @if ($this->budgets->isEmpty())
            <p class="mt-4 text-xs italic text-zinc-400 dark:text-zinc-500">
                No budget set for {{ $selectedMonthYear }}. Click "Set Budget" above to allocate monthly spending limits.
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

                            <div class="flex items-center gap-1.5">
                                <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $badgeColor }}">
                                    {{ number_format($percent, 1) }}%
                                </span>
                                <button
                                    type="button"
                                    wire:click="sendBudgetAlert({{ $budget->id }})"
                                    title="Send Budget Alert to Telegram"
                                    class="cursor-pointer rounded-lg p-1 text-zinc-400 transition hover:bg-zinc-200/60 hover:text-sky-600 dark:hover:bg-zinc-700"
                                >
                                    🔔
                                </button>
                                <button
                                    type="button"
                                    wire:click="editBudget({{ $budget->id }})"
                                    title="Edit Budget Limit"
                                    class="cursor-pointer rounded-lg p-1 text-zinc-400 transition hover:bg-zinc-200/60 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                >
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDeleteBudget({{ $budget->id }})"
                                    title="Delete Monthly Budget"
                                    class="cursor-pointer rounded-lg p-1 text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                                >
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300">
                            <span>Spent: <b>RM {{ number_format($spent, 2) }}</b></span>
                            <span>Limit: <b>RM {{ number_format($limit, 2) }}</b></span>
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
    <div class="rounded-3xl border border-zinc-200/80 bg-white p-5 sm:p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/90">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    🧾 Recent Transactions
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Real-time log of cash inflows, expenses, and inter-wallet transfers.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select
                    wire:model.live="filterType"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">All Types</option>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                    <option value="transfer">Transfer</option>
                </select>

                <select
                    wire:model.live="filterWalletId"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">All Wallets</option>
                    @foreach ($this->wallets as $wallet)
                        <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-y border-zinc-100 bg-zinc-50/60 uppercase text-zinc-400 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-500">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold">Date</th>
                        <th class="px-3 py-2.5 font-semibold">Category / Type</th>
                        <th class="px-3 py-2.5 font-semibold">Wallet</th>
                        <th class="px-3 py-2.5 font-semibold">Notes</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Amount</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Actions</th>
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
                                        <span>🔄</span> Wallet Transfer
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
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        type="button"
                                        wire:click="editTransaction({{ $transaction->id }})"
                                        class="cursor-pointer rounded-lg p-1 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                        title="Edit Transaction"
                                    >
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDeleteTransaction({{ $transaction->id }})"
                                        class="cursor-pointer rounded-lg p-1 text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                                        title="Delete Transaction"
                                    >
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs italic text-zinc-400">
                                No transaction records found.
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
                        {{ $editingTransactionId ? '✏️ Edit Transaction' : '⚡ Quick Transaction' }}
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
                            Expense
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickType('income')"
                            class="cursor-pointer rounded-lg py-2 text-xs font-bold transition {{ $transactionType === 'income' ? 'bg-emerald-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300' }}"
                        >
                            Income
                        </button>
                        <button
                            type="button"
                            wire:click="setQuickType('transfer')"
                            class="cursor-pointer rounded-lg py-2 text-xs font-bold transition {{ $transactionType === 'transfer' ? 'bg-sky-600 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-300' }}"
                        >
                            Transfer
                        </button>
                    </div>

                    {{-- Amount + Quick Presets --}}
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Amount (RM) *</label>
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
                                {{ $transactionType === 'transfer' ? 'From Wallet *' : 'Wallet *' }}
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
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">To Target Wallet *</label>
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
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Category *</label>
                                    <button
                                        type="button"
                                        wire:click="openCategoryModal('{{ $transactionType }}')"
                                        class="cursor-pointer text-[11px] font-bold text-amber-500 hover:text-amber-400"
                                    >
                                        + New Category
                                    </button>
                                </div>
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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Transaction Date *</label>
                            <input
                                type="date"
                                wire:model="transactionDate"
                                class="mt-1.5 w-full cursor-pointer rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-xs font-medium text-zinc-900 shadow-2xs transition hover:border-amber-400 focus:border-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:p-1 [&::-webkit-calendar-picker-indicator]:opacity-80 hover:[&::-webkit-calendar-picker-indicator]:opacity-100"
                            />
                            @error('transactionDate') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Notes (Optional)</label>
                            <input
                                type="text"
                                wire:model="transactionNotes"
                                placeholder="e.g. Lunch with client"
                                class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-xs text-zinc-900 shadow-2xs transition focus:border-amber-500 focus:outline-hidden focus:ring-2 focus:ring-amber-400/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeTransactionModal"
                            class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="cursor-pointer rounded-xl bg-zinc-900 px-5 py-2 text-xs font-bold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900"
                        >
                            {{ $editingTransactionId ? 'Update Transaction' : 'Save Transaction' }}
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
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $editingWalletId ? 'Edit Wallet' : 'Add New Wallet' }}</h3>
                    <button type="button" wire:click="$set('showWalletModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveWallet" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Wallet Name *</label>
                        <input
                            type="text"
                            wire:model="walletName"
                            placeholder="e.g. Maybank MAE / Touch 'n Go"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('walletName') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Wallet Type</label>
                            <select
                                wire:model.live="walletType"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="bank">Bank Account</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Starting Balance (RM)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="walletStartingBalance"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Wallet Icon / Emoji</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="walletIcon"
                                class="w-16 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-center text-lg text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            <div class="flex flex-wrap gap-1">
                                @foreach (['🏦', '📱', '💵', '💳', '🪙', '🏧', '💼', '💰', '📈', '🛒', '🏢', '🏷️'] as $emoji)
                                    <button
                                        type="button"
                                        wire:click="$set('walletIcon', '{{ $emoji }}')"
                                        class="cursor-pointer rounded-lg border border-zinc-200 bg-zinc-50 p-1.5 text-sm transition hover:border-amber-400 dark:border-zinc-700 dark:bg-zinc-800"
                                    >
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('walletIcon') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-3">
                        <button type="button" wire:click="$set('showWalletModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">{{ $editingWalletId ? 'Update Wallet' : 'Save Wallet' }}</button>
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
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $editingBudgetId ? 'Edit Monthly Budget' : 'Set Monthly Budget' }} ({{ $selectedMonthYear }})</h3>
                    <button type="button" wire:click="$set('showBudgetModal', false)" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveBudget" class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Expense Category *</label>
                            <button
                                type="button"
                                wire:click="openCategoryModal('expense')"
                                class="cursor-pointer text-[11px] font-bold text-amber-500 hover:text-amber-400"
                            >
                                + New Category
                            </button>
                        </div>
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
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Monthly Budget Limit (RM) *</label>
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
                        <button type="button" wire:click="$set('showBudgetModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">{{ $editingBudgetId ? 'Update Budget' : 'Save Budget' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Add / Manage Category --}}
    @if ($showCategoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-md">
            <div class="w-full max-w-lg rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-amber-500/15 text-sm">🏷️</span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ $editingCategoryId ? 'Edit Category' : 'Add & Manage Categories' }}</h3>
                    </div>
                    <button type="button" wire:click="$set('showCategoryModal', false)" class="cursor-pointer text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">✕</button>
                </div>

                <form wire:submit="saveCategory" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Category Name *</label>
                        <input
                            type="text"
                            wire:model="categoryName"
                            placeholder="e.g. Education, Health, Bonus, Business"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('categoryName') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Category Type *</label>
                            <select
                                wire:model="categoryType"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                            @error('categoryType') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Theme Color</label>
                            <select
                                wire:model="categoryColor"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="amber">Amber / Gold</option>
                                <option value="emerald">Emerald / Green</option>
                                <option value="rose">Rose / Red</option>
                                <option value="blue">Blue</option>
                                <option value="purple">Purple</option>
                                <option value="cyan">Cyan / Teal</option>
                                <option value="zinc">Zinc / Gray</option>
                            </select>
                            @error('categoryColor') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Icon / Emoji</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="categoryIcon"
                                class="w-16 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-center text-lg text-zinc-900 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            <div class="flex flex-wrap gap-1">
                                @foreach (['🏷️', '🍽️', '🚗', '💡', '🏥', '📚', '🎁', '💼', '🛒', '✈️', '🎮', '🏠'] as $emoji)
                                    <button
                                        type="button"
                                        wire:click="$set('categoryIcon', '{{ $emoji }}')"
                                        class="cursor-pointer rounded-lg border border-zinc-200 bg-zinc-50 p-1.5 text-sm hover:border-amber-400 dark:border-zinc-700 dark:bg-zinc-800"
                                    >
                                        {{ $emoji }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @error('categoryIcon') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showCategoryModal', false)" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">{{ $editingCategoryId ? 'Update Category' : 'Save Category' }}</button>
                    </div>
                </form>

                {{-- List of Custom Categories with Delete Action --}}
                @php
                    $customCategories = $this->categories->where('user_id', auth()->id());
                @endphp
                @if ($customCategories->isNotEmpty())
                    <div class="mt-6 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between pb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                Your Custom Categories ({{ $customCategories->count() }})
                            </h4>
                            <span class="text-[10px] text-zinc-400">Default categories are preserved</span>
                        </div>
                        <div class="max-h-40 space-y-1.5 overflow-y-auto pr-1">
                            @foreach ($customCategories as $cat)
                                <div class="flex items-center justify-between rounded-xl border border-zinc-100 bg-zinc-50/80 px-3 py-2 text-xs dark:border-zinc-800 dark:bg-zinc-800/60">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ $cat->icon ?? '🏷️' }}</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $cat->name }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $cat->type === 'expense' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $cat->type }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            wire:click="editCategory({{ $cat->id }})"
                                            title="Edit category"
                                            class="cursor-pointer rounded-md p-1 text-zinc-400 transition hover:bg-zinc-200/60 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                                        >
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="confirmDeleteCategory({{ $cat->id }})"
                                            title="Delete category"
                                            class="cursor-pointer rounded-md p-1 text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                                        >
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Flux Confirmation Delete Transaction Modal --}}
    <flux:modal wire:model.self="showDeleteModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Transaction</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this transaction record? Your wallet balances will be updated automatically.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteTransaction">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Flux Confirmation Delete Budget Modal --}}
    <flux:modal wire:model.self="showDeleteBudgetModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Monthly Budget</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete the monthly budget limit for "{{ $deletingBudgetCategory }}" in {{ $selectedMonthYear }}?
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteBudget">
                    Delete Budget
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Flux Confirmation Delete Wallet Modal --}}
    <flux:modal wire:model.self="showDeleteWalletModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Wallet</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete the wallet "{{ $deletingWalletName }}"? All transactions associated with this wallet will also be permanently deleted.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteWallet">
                    Delete Wallet
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Flux Confirmation Delete Category Modal --}}
    <flux:modal wire:model.self="showDeleteCategoryModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Category</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete the category "{{ $deletingCategoryName }}"? Any transactions using this category will become uncategorized.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteCategory">
                    Delete Category
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

