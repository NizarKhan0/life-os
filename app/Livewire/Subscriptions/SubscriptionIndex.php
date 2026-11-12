<?php

declare(strict_types=1);

namespace App\Livewire\Subscriptions;

use App\Models\Subscription;
use App\Models\Wallet;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Subscriptions & Commitments Vault — LifeOS')]
class SubscriptionIndex extends Component
{
    use WithPagination;

    public string $filterCategory = 'all';

    public string $filterStatus = 'active';

    // Modal Form State
    public bool $showSubscriptionModal = false;

    public bool $showDeleteModal = false;

    public ?int $deletingSubscriptionId = null;

    public ?int $editingSubscriptionId = null;

    public string $name = '';

    public string $category = 'digital';

    public string $amount = '';

    public string $billing_cycle = 'monthly';

    public string $next_billing_date = '';

    public ?int $wallet_id = null;

    public bool $auto_renew = true;

    public bool $is_active = true;

    public string $notes = '';

    public function mount(): void
    {
        $this->next_billing_date = Carbon::today()->addMonth()->toDateString();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Subscription>
     */
    #[Computed]
    public function subscriptions(): LengthAwarePaginator
    {
        return Subscription::query()
            ->with('wallet')
            ->where('user_id', Auth::id())
            ->when($this->filterCategory !== 'all', fn ($query) => $query->where('category', $this->filterCategory))
            ->when($this->filterStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(10);
    }

    /**
     * @return Collection<int, Wallet>
     */
    #[Computed]
    public function wallets(): Collection
    {
        return Wallet::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function totalMonthlyCommitment(): float
    {
        $activeSubs = Subscription::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return round(
            (float) $activeSubs->sum(fn (Subscription $sub): float => $sub->monthlyEquivalentAmount()),
            2
        );
    }

    #[Computed]
    public function totalYearlyCommitment(): float
    {
        return round($this->totalMonthlyCommitment() * 12.0, 2);
    }

    #[Computed]
    public function dueSoonCount(): int
    {
        return Subscription::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->get()
            ->filter(fn (Subscription $sub): bool => $sub->daysUntilDue() <= 3)
            ->count();
    }

    public function openCreateModal(): void
    {
        $this->resetSubscriptionForm();
        $firstWallet = $this->wallets()->first();
        $this->wallet_id = $firstWallet?->id;
        $this->next_billing_date = Carbon::today()->addDays(7)->toDateString();
        $this->showSubscriptionModal = true;
    }

    public function editSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::query()
            ->where('user_id', Auth::id())
            ->findOrFail($subscriptionId);

        $this->editingSubscriptionId = $subscription->id;
        $this->name = $subscription->name;
        $this->category = $subscription->category;
        $this->amount = (string) (float) $subscription->amount;
        $this->billing_cycle = $subscription->billing_cycle;
        $this->next_billing_date = $subscription->next_billing_date->format('Y-m-d');
        $this->wallet_id = $subscription->wallet_id;
        $this->auto_renew = $subscription->auto_renew;
        $this->is_active = $subscription->is_active;
        $this->notes = (string) ($subscription->notes ?? '');
        $this->showSubscriptionModal = true;
    }

    public function closeSubscriptionModal(): void
    {
        $this->showSubscriptionModal = false;
        $this->resetSubscriptionForm();
    }

    public function saveSubscription(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:digital,utility,insurance,government'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
            'next_billing_date' => ['required', 'date'],
            'wallet_id' => ['nullable', 'integer', 'exists:wallets,id'],
            'auto_renew' => ['boolean'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'amount' => (float) $validated['amount'],
            'billing_cycle' => $validated['billing_cycle'],
            'next_billing_date' => $validated['next_billing_date'],
            'wallet_id' => $validated['wallet_id'] !== null ? (int) $validated['wallet_id'] : null,
            'auto_renew' => (bool) $validated['auto_renew'],
            'is_active' => (bool) $validated['is_active'],
            'notes' => trim((string) $validated['notes']) !== '' ? trim((string) $validated['notes']) : null,
        ];

        if ($this->editingSubscriptionId !== null) {
            $subscription = Subscription::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingSubscriptionId);

            $subscription->update($payload);
            Flux::toast(variant: 'success', text: 'Commitment updated successfully.');
        } else {
            Subscription::query()->create([
                ...$payload,
                'user_id' => (int) Auth::id(),
            ]);
            Flux::toast(variant: 'success', text: 'New commitment added successfully.');
        }

        $this->refreshComputedProperties();
        $this->closeSubscriptionModal();
    }

    /**
     * Advance next_billing_date by 1 billing cycle (1 month or 1 year).
     */
    public function renewNextCycle(int $subscriptionId): void
    {
        $subscription = Subscription::query()
            ->where('user_id', Auth::id())
            ->findOrFail($subscriptionId);

        $nextDate = $subscription->billing_cycle === 'yearly'
            ? $subscription->next_billing_date->copy()->addYear()
            : $subscription->next_billing_date->copy()->addMonth();

        $subscription->update([
            'next_billing_date' => $nextDate->toDateString(),
        ]);

        $this->refreshComputedProperties();

        Flux::toast(
            variant: 'success',
            text: "Billing date for {$subscription->name} advanced to ".$nextDate->format('d M Y').'.'
        );
    }

    /**
     * Toggle active state of a Subscription.
     */
    public function toggleActive(int $subscriptionId): void
    {
        $subscription = Subscription::query()
            ->where('user_id', Auth::id())
            ->findOrFail($subscriptionId);

        $newState = ! $subscription->is_active;

        $subscription->update([
            'is_active' => $newState,
        ]);

        $this->refreshComputedProperties();

        Flux::toast(
            variant: $newState ? 'success' : 'warning',
            text: $newState ? 'Subscription activated.' : 'Subscription paused.'
        );
    }

    public function confirmDeleteSubscription(int $subscriptionId): void
    {
        $this->deletingSubscriptionId = $subscriptionId;
        $this->showDeleteModal = true;
    }

    public function executeDeleteSubscription(): void
    {
        if ($this->deletingSubscriptionId === null) {
            return;
        }

        $subscription = Subscription::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingSubscriptionId);

        $subscription->delete();
        $this->deletingSubscriptionId = null;
        $this->showDeleteModal = false;
        $this->refreshComputedProperties();

        Flux::toast(variant: 'success', text: 'Commitment deleted successfully.');
    }

    public function sendTelegramAlert(int $subscriptionId, TelegramService $telegramService): void
    {
        $subscription = Subscription::query()
            ->with('wallet')
            ->where('user_id', Auth::id())
            ->findOrFail($subscriptionId);

        if ($telegramService->sendSubscriptionDueAlert($subscription)) {
            Flux::toast(variant: 'success', text: 'Commitment alert sent to Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Simulation recorded in log (Set TELEGRAM_BOT_TOKEN in .env).');
        }
    }

    private function refreshComputedProperties(): void
    {
        unset(
            $this->subscriptions,
            $this->totalMonthlyCommitment,
            $this->totalYearlyCommitment,
            $this->dueSoonCount
        );
    }

    private function resetSubscriptionForm(): void
    {
        $this->editingSubscriptionId = null;
        $this->name = '';
        $this->category = 'digital';
        $this->amount = '';
        $this->billing_cycle = 'monthly';
        $this->next_billing_date = Carbon::today()->addDays(7)->toDateString();
        $this->wallet_id = null;
        $this->auto_renew = true;
        $this->is_active = true;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.subscriptions.subscription-index');
    }
}
