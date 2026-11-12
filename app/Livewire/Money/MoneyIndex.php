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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Money & Budget Management — LifeOS')]
class MoneyIndex extends Component
{
    use WithPagination;

    public string $selectedMonthYear = '';

    public string $filterType = 'all';

    public string $filterWalletId = 'all';

    // Quick Transaction Modal State
    public bool $showTransactionModal = false;

    public ?int $editingTransactionId = null;

    public bool $showDeleteModal = false;

    public ?int $deletingTransactionId = null;

    public string $transactionType = 'expense';

    public string $transactionAmount = '';

    public ?int $transactionWalletId = null;

    public ?int $transactionCategoryId = null;

    public ?int $transactionTargetWalletId = null;

    public string $transactionDate = '';

    public string $transactionNotes = '';

    // Wallet Modal State
    public bool $showWalletModal = false;

    public ?int $editingWalletId = null;

    public bool $showDeleteWalletModal = false;

    public ?int $deletingWalletId = null;

    public ?string $deletingWalletName = null;

    public string $walletName = '';

    public string $walletType = 'bank';

    public string $walletIcon = '🏦';

    public string $walletStartingBalance = '0';

    public string $walletCurrency = 'MYR';

    // Budget Modal State
    public bool $showBudgetModal = false;

    public ?int $editingBudgetId = null;

    public bool $showDeleteBudgetModal = false;

    public ?int $deletingBudgetId = null;

    public ?string $deletingBudgetCategory = null;

    public ?int $budgetCategoryId = null;

    public string $budgetAmount = '';

    // Category Modal State
    public bool $showCategoryModal = false;

    public ?int $editingCategoryId = null;

    public bool $showDeleteCategoryModal = false;

    public ?int $deletingCategoryId = null;

    public ?string $deletingCategoryName = null;

    public string $categoryName = '';

    public string $categoryType = 'expense';

    public string $categoryIcon = '🏷️';

    public string $categoryColor = 'amber';

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

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterWalletId(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Transaction>
     */
    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        return Transaction::query()
            ->with(['wallet', 'category', 'targetWallet'])
            ->where('user_id', Auth::id())
            ->when($this->filterType !== 'all', fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterWalletId !== 'all', fn ($query) => $query->where('wallet_id', (int) $this->filterWalletId))
            ->latest('id')
            ->paginate(10);
    }

    public function openQuickTransactionModal(string $type = 'expense'): void
    {
        $this->resetValidation();
        $this->editingTransactionId = null;
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

    public function editTransaction(int $transactionId): void
    {
        $this->resetValidation();
        $transaction = Transaction::query()
            ->where('user_id', Auth::id())
            ->findOrFail($transactionId);

        $this->editingTransactionId = $transaction->id;
        $this->transactionType = $transaction->type;
        $this->transactionAmount = (string) $transaction->amount;
        $this->transactionWalletId = $transaction->wallet_id;
        $this->transactionCategoryId = $transaction->category_id;
        $this->transactionTargetWalletId = $transaction->target_wallet_id;
        $this->transactionDate = $transaction->transaction_date->toDateString();
        $this->transactionNotes = $transaction->notes ?? '';
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

        $payload = [
            'wallet_id' => $wallet->id,
            'category_id' => $this->transactionType === 'transfer' ? null : $this->transactionCategoryId,
            'type' => $this->transactionType,
            'amount' => (float) $validated['transactionAmount'],
            'transaction_date' => $validated['transactionDate'],
            'notes' => $validated['transactionNotes'] !== '' ? $validated['transactionNotes'] : null,
            'target_wallet_id' => $targetWalletId,
        ];

        if ($this->editingTransactionId !== null) {
            $transaction = Transaction::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingTransactionId);

            $transaction->update($payload);
            $this->editingTransactionId = null;
            $toastText = 'Transaction updated successfully.';
        } else {
            $transaction = Transaction::query()->create([
                'user_id' => (int) Auth::id(),
                ...$payload,
            ]);
            $toastText = 'Transaction recorded successfully.';
        }

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

        Flux::toast(variant: 'success', text: $toastText);
    }

    public function confirmDeleteTransaction(int $transactionId): void
    {
        $this->deletingTransactionId = $transactionId;
        $this->showDeleteModal = true;
    }

    public function deleteTransaction(int $transactionId): void
    {
        $this->confirmDeleteTransaction($transactionId);
    }

    public function executeDeleteTransaction(): void
    {
        if ($this->deletingTransactionId === null) {
            return;
        }

        $transaction = Transaction::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingTransactionId);

        $transaction->delete();
        $this->deletingTransactionId = null;
        $this->showDeleteModal = false;

        unset($this->wallets, $this->netWorth, $this->transactions, $this->budgets);

        Flux::toast(variant: 'success', text: 'Transaction deleted successfully.');
    }

    public function updatedWalletType(string $value): void
    {
        $this->walletIcon = match ($value) {
            'bank' => '🏦',
            'ewallet' => '📱',
            default => '💵',
        };
    }

    public function openWalletModal(): void
    {
        $this->resetValidation();
        $this->editingWalletId = null;
        $this->walletName = '';
        $this->walletType = 'bank';
        $this->walletIcon = '🏦';
        $this->walletStartingBalance = '0';
        $this->walletCurrency = 'MYR';
        $this->showWalletModal = true;
    }

    public function editWallet(int $walletId): void
    {
        $this->resetValidation();
        $wallet = Wallet::query()
            ->where('user_id', Auth::id())
            ->findOrFail($walletId);

        $this->editingWalletId = $wallet->id;
        $this->walletName = $wallet->name;
        $this->walletType = $wallet->type;
        $this->walletIcon = $wallet->displayIcon();
        $this->walletStartingBalance = (string) $wallet->starting_balance;
        $this->walletCurrency = $wallet->currency;
        $this->showWalletModal = true;
    }

    public function saveWallet(): void
    {
        $validated = $this->validate([
            'walletName' => ['required', 'string', 'max:100'],
            'walletType' => ['required', 'string', 'in:bank,ewallet,cash'],
            'walletIcon' => ['nullable', 'string', 'max:10'],
            'walletStartingBalance' => ['required', 'numeric'],
            'walletCurrency' => ['required', 'string', 'max:10'],
        ]);

        $payload = [
            'name' => $validated['walletName'],
            'type' => $validated['walletType'],
            'icon' => $validated['walletIcon'] ?: '🏦',
            'starting_balance' => (float) $validated['walletStartingBalance'],
            'currency' => strtoupper($validated['walletCurrency']),
        ];

        if ($this->editingWalletId !== null) {
            $wallet = Wallet::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingWalletId);

            $wallet->update($payload);
            $this->editingWalletId = null;
            $toastText = 'Wallet updated successfully.';
        } else {
            Wallet::query()->create([
                'user_id' => (int) Auth::id(),
                ...$payload,
            ]);
            $toastText = 'New wallet added successfully.';
        }

        unset($this->wallets, $this->netWorth);
        $this->showWalletModal = false;

        Flux::toast(variant: 'success', text: $toastText);
    }

    public function confirmDeleteWallet(int $walletId): void
    {
        $wallet = Wallet::query()
            ->where('user_id', Auth::id())
            ->findOrFail($walletId);

        $this->deletingWalletId = $wallet->id;
        $this->deletingWalletName = $wallet->name;
        $this->showDeleteWalletModal = true;
    }

    public function executeDeleteWallet(): void
    {
        if ($this->deletingWalletId === null) {
            return;
        }

        $wallet = Wallet::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingWalletId);

        $wallet->delete();

        $this->deletingWalletId = null;
        $this->deletingWalletName = null;
        $this->showDeleteWalletModal = false;

        unset($this->wallets, $this->netWorth, $this->transactions);

        Flux::toast(variant: 'success', text: 'Wallet deleted successfully.');
    }

    public function openBudgetModal(): void
    {
        $this->resetValidation();
        $this->editingBudgetId = null;
        $firstExpenseCategory = $this->categories()->where('type', 'expense')->first();
        $this->budgetCategoryId = $firstExpenseCategory?->id;
        $this->budgetAmount = '';
        $this->showBudgetModal = true;
    }

    public function editBudget(int $budgetId): void
    {
        $this->resetValidation();
        $budget = Budget::query()
            ->where('user_id', Auth::id())
            ->findOrFail($budgetId);

        $this->editingBudgetId = $budget->id;
        $this->budgetCategoryId = $budget->category_id;
        $this->budgetAmount = (string) $budget->amount;
        $this->showBudgetModal = true;
    }

    public function saveBudget(): void
    {
        $validated = $this->validate([
            'budgetCategoryId' => ['required', 'integer', 'exists:categories,id'],
            'budgetAmount' => ['required', 'numeric', 'gt:0'],
        ]);

        if ($this->editingBudgetId !== null) {
            $budget = Budget::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingBudgetId);

            $budget->update([
                'category_id' => (int) $validated['budgetCategoryId'],
                'amount' => (float) $validated['budgetAmount'],
            ]);

            $this->editingBudgetId = null;
            $toastText = 'Monthly budget updated successfully.';
        } else {
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
            $toastText = 'Monthly budget saved successfully.';
        }

        unset($this->budgets);
        $this->showBudgetModal = false;

        Flux::toast(variant: 'success', text: $toastText);
    }

    public function confirmDeleteBudget(int $budgetId): void
    {
        $budget = Budget::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($budgetId);

        $this->deletingBudgetId = $budget->id;
        $this->deletingBudgetCategory = $budget->category->name ?? 'Selected category';
        $this->showDeleteBudgetModal = true;
    }

    public function executeDeleteBudget(): void
    {
        if ($this->deletingBudgetId === null) {
            return;
        }

        $budget = Budget::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingBudgetId);

        $budget->delete();

        $this->deletingBudgetId = null;
        $this->deletingBudgetCategory = null;
        $this->showDeleteBudgetModal = false;

        unset($this->budgets);

        Flux::toast(variant: 'success', text: 'Monthly budget deleted successfully.');
    }

    public function sendBudgetAlert(int $budgetId, TelegramService $telegramService): void
    {
        $budget = Budget::query()
            ->with('category')
            ->where('user_id', Auth::id())
            ->findOrFail($budgetId);

        if ($telegramService->sendBudgetAlert($budget)) {
            Flux::toast(variant: 'success', text: 'Budget alert sent to Telegram!');
        } else {
            Flux::toast(variant: 'warning', text: 'Budget alert simulated in log (Set TELEGRAM_BOT_TOKEN in .env).');
        }
    }

    public function openCategoryModal(string $type = 'expense'): void
    {
        $this->resetValidation();
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryType = in_array($type, ['expense', 'income'], true) ? $type : 'expense';
        $this->categoryIcon = '🏷️';
        $this->categoryColor = 'amber';
        $this->showCategoryModal = true;
    }

    public function editCategory(int $categoryId): void
    {
        $this->resetValidation();
        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail($categoryId);

        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryType = $category->type;
        $this->categoryIcon = $category->icon ?? '🏷️';
        $this->categoryColor = $category->color ?? 'amber';
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:50'],
            'categoryType' => ['required', 'string', 'in:expense,income'],
            'categoryIcon' => ['required', 'string', 'max:10'],
            'categoryColor' => ['required', 'string', 'max:20'],
        ]);

        $payload = [
            'name' => $validated['categoryName'],
            'type' => $validated['categoryType'],
            'icon' => $validated['categoryIcon'],
            'color' => $validated['categoryColor'],
        ];

        if ($this->editingCategoryId !== null) {
            $category = Category::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingCategoryId);

            $category->update($payload);
            $this->editingCategoryId = null;
            $toastText = 'Category updated successfully.';
        } else {
            $category = Category::query()->create([
                'user_id' => (int) Auth::id(),
                ...$payload,
            ]);
            $toastText = 'New category created successfully.';
        }

        unset($this->categories);
        $this->transactionCategoryId = $category->id;
        $this->budgetCategoryId = $category->id;
        $this->showCategoryModal = false;

        Flux::toast(variant: 'success', text: $toastText);
    }

    public function confirmDeleteCategory(int $categoryId): void
    {
        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail($categoryId);

        $this->deletingCategoryId = $category->id;
        $this->deletingCategoryName = $category->name;
        $this->showDeleteCategoryModal = true;
    }

    public function executeDeleteCategory(): void
    {
        if ($this->deletingCategoryId === null) {
            return;
        }

        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->deletingCategoryId);

        $category->delete();

        $this->deletingCategoryId = null;
        $this->deletingCategoryName = null;
        $this->showDeleteCategoryModal = false;

        unset($this->categories, $this->budgets);

        Flux::toast(variant: 'success', text: 'Category deleted successfully.');
    }

    public function render(): View
    {
        return view('livewire.money.money-index');
    }
}
