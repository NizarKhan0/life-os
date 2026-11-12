<?php

declare(strict_types=1);

namespace App\Livewire\Money;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TelegramService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Money & Budget Management — LifeOS')]
class MoneyIndex extends Component
{
    public string $selectedMonthYear = '';

    public string $filterType = 'all';

    public string $filterWalletId = 'all';

    // Quick Transaction Modal State
    public bool $showTransactionModal = false;

    public string $transactionType = 'expense';

    public string $transactionAmount = '';

    public ?int $transactionWalletId = null;

    public ?int $transactionCategoryId = null;

    public ?int $transactionTargetWalletId = null;

    public string $transactionDate = '';

    public string $transactionNotes = '';

    // Wallet Modal State
    public bool $showWalletModal = false;

    public string $walletName = '';

    public string $walletType = 'bank';

    public string $walletStartingBalance = '0';

    public string $walletCurrency = 'MYR';

    // Budget Modal State
    public bool $showBudgetModal = false;

    public ?int $budgetCategoryId = null;

    public string $budgetAmount = '';

    public function mount(): void
    {
        $this->selectedMonthYear = Carbon::now('Asia/Kuala_Lumpur')->format('Y-m');
        $this->transactionDate = Carbon::now('Asia/Kuala_Lumpur')->toDateString();
    }

    /**
     * @return Collection<int, Wallet>
     */
    #[Computed]
    public function wallets(): Collection
    {
        return Wallet::query()
            ->with(['transactions', 'incomingTransfers'])
            ->where('user_id', Auth::id())
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function netWorth(): float
    {
        return round(
            (float) $this->wallets()->sum(fn (Wallet $wallet): float => $wallet->currentBalance()),
            2
        );
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->where(function ($query): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Budget>
     */
    #[Computed]
    public function budgets(): Collection
    {
        return Budget::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->where('month_year', $this->selectedMonthYear)
            ->get();
    }

    /**
     * @return Collection<int, Transaction>
     */
    #[Computed]
    public function transactions(): Collection
    {
        return Transaction::query()
            ->with(['wallet', 'category', 'targetWallet'])
            ->where('user_id', Auth::id())
            ->when($this->filterType !== 'all', fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterWalletId !== 'all', fn ($query) => $query->where('wallet_id', (int) $this->filterWalletId))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(30)
            ->get();
    }

    public function openQuickTransactionModal(string $type = 'expense'): void
    {
        $this->resetValidation();
        $this->transactionType = in_array($type, ['expense', 'income', 'transfer'], true) ? $type : 'expense';
        $this->transactionAmount = '';
        $this->transactionDate = Carbon::now('Asia/Kuala_Lumpur')->toDateString();
        $this->transactionNotes = '';

        $wallets = $this->wallets();
        $categories = $this->categories();

        $firstWallet = $wallets->first();
        $this->transactionWalletId = $firstWallet?->id;

        $secondWallet = $wallets->skip(1)->first();
        $this->transactionTargetWalletId = $secondWallet?->id;

        $defaultCategory = $categories->where('type', $this->transactionType === 'income' ? 'income' : 'expense')->first();
        $this->transactionCategoryId = $defaultCategory?->id;

        $this->showTransactionModal = true;
    }

    public function setQuickType(string $type): void
    {
        if (! in_array($type, ['expense', 'income', 'transfer'], true)) {
            return;
        }

        $this->transactionType = $type;

        if ($type !== 'transfer') {
            $defaultCategory = $this->categories()->where('type', $type)->first();
            $this->transactionCategoryId = $defaultCategory?->id;
        }
    }

    public function setPresetAmount(int $amount): void
    {
        $this->transactionAmount = (string) $amount;
    }

    public function closeTransactionModal(): void
    {
        $this->showTransactionModal = false;
    }

    public function saveTransaction(TelegramService $telegramService): void
    {
        $rules = [
            'transactionType' => ['required', 'string', 'in:expense,income,transfer'],
            'transactionAmount' => ['required', 'numeric', 'gt:0'],
            'transactionWalletId' => ['required', 'integer', 'exists:wallets,id'],
            'transactionDate' => ['required', 'date'],
            'transactionNotes' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->transactionType === 'transfer') {
            $rules['transactionTargetWalletId'] = ['required', 'integer', 'exists:wallets,id', 'different:transactionWalletId'];
        } else {
            $rules['transactionCategoryId'] = ['required', 'integer', 'exists:categories,id'];
        }

        $validated = $this->validate($rules);

        $wallet = Wallet::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $validated['transactionWalletId']);

        $targetWalletId = null;
        if ($this->transactionType === 'transfer' && $this->transactionTargetWalletId !== null) {
            $targetWallet = Wallet::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->transactionTargetWalletId);
            $targetWalletId = $targetWallet->id;
        }

        $transaction = Transaction::query()->create([
            'user_id' => (int) Auth::id(),
            'wallet_id' => $wallet->id,
            'category_id' => $this->transactionType === 'transfer' ? null : $this->transactionCategoryId,
            'type' => $this->transactionType,
            'amount' => (float) $validated['transactionAmount'],
            'transaction_date' => $validated['transactionDate'],
            'notes' => $validated['transactionNotes'] !== '' ? $validated['transactionNotes'] : null,
            'target_wallet_id' => $targetWalletId,
        ]);

        // Check if an expense triggers a budget alert (>= 80%)
        if ($transaction->type === 'expense' && $transaction->category_id !== null) {
            $monthYear = $transaction->transaction_date->format('Y-m');
            $budget = Budget::query()
                ->with('category')
                ->where('user_id', Auth::id())
                ->where('category_id', $transaction->category_id)
                ->where('month_year', $monthYear)
                ->first();

            if ($budget !== null && $budget->progressPercentage() >= 80.0) {
                $telegramService->sendBudgetAlert($budget);
            }
        }

        unset($this->wallets, $this->netWorth, $this->transactions, $this->budgets);
        $this->showTransactionModal = false;

        Flux::toast(variant: 'success', text: 'Transaksi berjaya direkodkan.');
    }

    public function deleteTransaction(int $transactionId): void
    {
        $transaction = Transaction::query()
            ->where('user_id', Auth::id())
            ->findOrFail($transactionId);

        $transaction->delete();

        unset($this->wallets, $this->netWorth, $this->transactions, $this->budgets);

        Flux::toast(variant: 'success', text: 'Transaksi telah dipadam.');
    }

    public function openWalletModal(): void
    {
        $this->resetValidation();
        $this->walletName = '';
        $this->walletType = 'bank';
        $this->walletStartingBalance = '0';
        $this->walletCurrency = 'MYR';
        $this->showWalletModal = true;
    }

    public function saveWallet(): void
    {
        $validated = $this->validate([
            'walletName' => ['required', 'string', 'max:100'],
            'walletType' => ['required', 'string', 'in:bank,ewallet,cash'],
            'walletStartingBalance' => ['required', 'numeric'],
            'walletCurrency' => ['required', 'string', 'max:10'],
        ]);

        Wallet::query()->create([
            'user_id' => (int) Auth::id(),
            'name' => $validated['walletName'],
            'type' => $validated['walletType'],
            'starting_balance' => (float) $validated['walletStartingBalance'],
            'currency' => strtoupper($validated['walletCurrency']),
        ]);

        unset($this->wallets, $this->netWorth);
        $this->showWalletModal = false;

        Flux::toast(variant: 'success', text: 'Dompet baharu berjaya ditambah.');
    }

    public function openBudgetModal(): void
    {
        $this->resetValidation();
        $firstExpenseCategory = $this->categories()->where('type', 'expense')->first();
        $this->budgetCategoryId = $firstExpenseCategory?->id;
        $this->budgetAmount = '';
        $this->showBudgetModal = true;
    }

    public function saveBudget(): void
    {
        $validated = $this->validate([
            'budgetCategoryId' => ['required', 'integer', 'exists:categories,id'],
            'budgetAmount' => ['required', 'numeric', 'gt:0'],
        ]);

        Budget::query()->updateOrCreate(
            [
                'user_id' => (int) Auth::id(),
                'category_id' => (int) $validated['budgetCategoryId'],
                'month_year' => $this->selectedMonthYear,
            ],
            [
                'amount' => (float) $validated['budgetAmount'],
            ]
        );

        unset($this->budgets);
        $this->showBudgetModal = false;

        Flux::toast(variant: 'success', text: 'Bajet bulanan berjaya disimpan.');
    }

    public function sendBudgetAlert(int $budgetId, TelegramService $telegramService): void
    {
        $budget = Budget::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($budgetId);

        if ($telegramService->sendBudgetAlert($budget)) {
            Flux::toast(variant: 'success', text: 'Amaran bajet dihantar ke Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Simulasi amaran bajet direkodkan dalam log (Tetapkan TELEGRAM_BOT_TOKEN dalam .env).');
        }
    }

    public function render(): View
    {
        return view('livewire.money.money-index');
    }
}
