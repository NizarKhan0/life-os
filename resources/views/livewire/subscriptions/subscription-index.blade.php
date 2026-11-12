<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                💳 Subscriptions & Commitments Vault
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Audit recurring SaaS bills, utilities, and auto-debits with 3-day Telegram renewal alerts.
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
            Add Commitment
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Monthly Commitment Card --}}
        <div class="flex flex-col justify-between rounded-2xl bg-linear-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-5 text-white shadow-sm dark:from-zinc-100 dark:via-white dark:to-zinc-200 dark:text-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Monthly Burn Rate
                </span>
                <span class="rounded-lg bg-white/10 px-2 py-0.5 text-xs font-medium dark:bg-zinc-900/10">
                    MYR / Month
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-extrabold tracking-tight">
                    RM {{ number_format($this->totalMonthlyCommitment, 2) }}
                </p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-600">
                    Includes monthly amortized yearly commitments
                </p>
            </div>
        </div>

        {{-- Yearly Projection Card --}}
        <div class="flex flex-col justify-between rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    Yearly Projected Burn
                </span>
                <span class="text-base">📅</span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    RM {{ number_format($this->totalYearlyCommitment, 2) }}
                </p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    Estimated 12-month recurring expenditure
                </p>
            </div>
        </div>

        {{-- Due Soon Warning Card --}}
        <div class="sm:col-span-2 lg:col-span-1 flex flex-col justify-between rounded-2xl border p-5 shadow-xs {{ $this->dueSoonCount > 0 ? 'border-amber-300 bg-amber-50/50 dark:border-amber-700/60 dark:bg-amber-950/20' : 'border-zinc-200 bg-white dark:border-zinc-700/80 dark:bg-zinc-900' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $this->dueSoonCount > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-zinc-400 dark:text-zinc-500' }}">
                    Renewal Due (≤ 3 Days)
                </span>
                <span class="text-base">{{ $this->dueSoonCount > 0 ? '⚠️' : '✅' }}</span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold {{ $this->dueSoonCount > 0 ? 'text-amber-800 dark:text-amber-200' : 'text-zinc-900 dark:text-zinc-100' }}">
                    {{ $this->dueSoonCount }} Commitments
                </p>
                <p class="mt-1 text-xs {{ $this->dueSoonCount > 0 ? 'text-amber-700/80 dark:text-amber-300/80' : 'text-zinc-500 dark:text-zinc-400' }}">
                    Evaluated daily at 09:00 AM
                </p>
            </div>
        </div>
    </div>

    {{-- Filters & Subscriptions Table --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4">
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $categories = [
                        'all' => 'All Categories',
                        'digital' => '💻 Digital',
                        'utility' => '⚡ Utility',
                        'insurance' => '🛡️ Insurance',
                        'government' => '🏛️ Gov / Tax',
                    ];
                @endphp

                @foreach ($categories as $key => $label)
                    <button
                        type="button"
                        wire:click="$set('filterCategory', '{{ $key }}')"
                        class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-semibold transition {{ $filterCategory === $key ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div>
                <select
                    wire:model.live="filterStatus"
                    class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-semibold text-zinc-800 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="all">All Status</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive / Cancelled</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-y border-zinc-100 bg-zinc-50/60 uppercase text-zinc-400 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-500">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold">Subscription / Name</th>
                        <th class="px-3 py-2.5 font-semibold">Category</th>
                        <th class="px-3 py-2.5 font-semibold">Wallet & Method</th>
                        <th class="px-3 py-2.5 font-semibold">Next Billing & Status</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Amount</th>
                        <th class="px-3 py-2.5 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->subscriptions as $subscription)
                        @php
                            $days = $subscription->daysUntilDue();
                            $dueSoon = $subscription->isDueSoon(3);

                            $categoryBadge = match ($subscription->category) {
                                'digital' => 'bg-sky-500/10 text-sky-700 ring-sky-500/20 dark:text-sky-300',
                                'utility' => 'bg-amber-500/10 text-amber-700 ring-amber-500/20 dark:text-amber-300',
                                'insurance' => 'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300',
                                default => 'bg-violet-500/10 text-violet-700 ring-violet-500/20 dark:text-violet-300',
                            };

                            $dueBadgeStyle = match (true) {
                                ! $subscription->is_active => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400',
                                $days < 0 => 'bg-rose-500/15 text-rose-700 ring-1 ring-inset ring-rose-500/30 dark:text-rose-300',
                                $days === 0 => 'bg-rose-500/15 text-rose-700 ring-1 ring-inset ring-rose-500/30 dark:text-rose-300',
                                $dueSoon => 'bg-amber-500/15 text-amber-700 ring-1 ring-inset ring-amber-500/30 dark:text-amber-300',
                                default => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                            };

                            $dueBadgeText = match (true) {
                                ! $subscription->is_active => 'Inactive',
                                $days < 0 => '🚨 Overdue by '.abs($days).' days',
                                $days === 0 => '⚠️ Due Today!',
                                $dueSoon => "⏳ {$days} days left",
                                default => "{$days} days left",
                            };
                        @endphp

                        <tr wire:key="sub-row-{{ $subscription->id }}" class="transition hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 {{ ! $subscription->is_active ? 'opacity-60' : '' }}">
                            <td class="px-3 py-3.5">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $subscription->name }}
                                </div>
                                @if ($subscription->notes)
                                    <div class="mt-0.5 text-[11px] text-zinc-400 dark:text-zinc-500">
                                        {{ $subscription->notes }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-3.5">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $categoryBadge }}">
                                    {{ ucfirst($subscription->category) }}
                                </span>
                            </td>

                            <td class="px-3 py-3.5">
                                <div class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ $subscription->wallet?->name ?? 'No specific wallet' }}
                                </div>
                                <div class="mt-0.5 text-[11px] text-zinc-400">
                                    {{ $subscription->auto_renew ? '🔄 Auto-Renew' : '🖐 Manual Payment' }}
                                </div>
                            </td>

                            <td class="px-3 py-3.5">
                                <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $subscription->next_billing_date->format('d M Y') }}
                                </div>
                                <span class="mt-1 inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $dueBadgeStyle }}">
                                    {{ $dueBadgeText }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-right">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">
                                    RM {{ number_format((float) $subscription->amount, 2) }}
                                </div>
                                <div class="text-[10px] uppercase text-zinc-400">
                                    {{ $subscription->billing_cycle === 'yearly' ? 'Yearly' : 'Monthly' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        wire:click="renewNextCycle({{ $subscription->id }})"
                                        title="Mark Paid & Advance to Next Billing Cycle"
                                        class="cursor-pointer rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                                    >
                                        ✓ Mark Paid
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="sendTelegramAlert({{ $subscription->id }})"
                                        title="Send Telegram Alert"
                                        class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-sky-600 dark:hover:bg-zinc-800"
                                    >
                                        🔔
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="editSubscription({{ $subscription->id }})"
                                        title="Edit Commitment"
                                        class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                    >
                                        ✏️
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="toggleActive({{ $subscription->id }})"
                                        title="{{ $subscription->is_active ? 'Pause Commitment' : 'Reactivate' }}"
                                        class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                    >
                                        {{ $subscription->is_active ? '⏸️' : '▶️' }}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="confirmDeleteSubscription({{ $subscription->id }})"
                                        title="Delete Subscription"
                                        class="cursor-pointer rounded-lg p-1.5 text-zinc-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs italic text-zinc-400">
                                No recurring subscriptions or commitments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->subscriptions->hasPages())
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                {{ $this->subscriptions->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Add / Edit Subscription --}}
    @if ($showSubscriptionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $editingSubscriptionId ? 'Update Commitment' : 'Add New Commitment / Subscription' }}
                    </h3>
                    <button type="button" wire:click="closeSubscriptionModal" class="cursor-pointer text-zinc-400">✕</button>
                </div>

                <form wire:submit="saveSubscription" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Subscription / Commitment Name *</label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="e.g. TIME Fibre 500Mbps / Netflix / AWS Cloud"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        />
                        @error('name') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Category *</label>
                            <select
                                wire:model="category"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="digital">💻 Digital (SaaS / Cloud / Streaming)</option>
                                <option value="utility">⚡ Utility (Internet / Electric / Bills)</option>
                                <option value="insurance">🛡️ Insurance / Takaful</option>
                                <option value="government">🏛️ Government / Tax / Roadtax</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Billing Cycle *</label>
                            <select
                                wire:model="billing_cycle"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Amount (RM) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                wire:model="amount"
                                placeholder="139.00"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('amount') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Next Billing Date *</label>
                            <input
                                type="date"
                                wire:model="next_billing_date"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            @error('next_billing_date') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Assigned Wallet (Optional)</label>
                        <select
                            wire:model="wallet_id"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option value="">-- No specific wallet --</option>
                            @foreach ($this->wallets as $wallet)
                                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-6 pt-1">
                        <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="auto_renew" class="size-4 rounded border-zinc-300 text-zinc-900" />
                            Auto-Renew (Automatic Deduction)
                        </label>

                        <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" wire:model="is_active" class="size-4 rounded border-zinc-300 text-zinc-900" />
                            Active Status
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Notes / Account Reference</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            placeholder="Account reference, subscription tier, or reminder details..."
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button type="button" wire:click="closeSubscriptionModal" class="cursor-pointer rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">Cancel</button>
                        <button type="submit" class="cursor-pointer rounded-xl bg-zinc-900 px-4 py-2 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">
                            {{ $editingSubscriptionId ? 'Save Changes' : 'Save Commitment' }}
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
                <flux:heading size="lg">Delete Subscription</flux:heading>
                <flux:subheading>
                    Are you sure you want to delete this commitment? This record will be permanently removed from your vault.
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" wire:click="executeDeleteSubscription">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
