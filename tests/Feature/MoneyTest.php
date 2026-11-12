<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Money\MoneyIndex;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('money.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_money_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('money.index'));
        $response->assertOk();
    }

    public function test_user_can_create_new_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(MoneyIndex::class)
            ->call('openCategoryModal', 'expense')
            ->assertSet('showCategoryModal', true)
            ->set('categoryName', 'Pendidikan & Kursus')
            ->set('categoryType', 'expense')
            ->set('categoryIcon', '📚')
            ->set('categoryColor', 'purple')
            ->call('saveCategory')
            ->assertSet('showCategoryModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Pendidikan & Kursus',
            'type' => 'expense',
            'icon' => '📚',
            'color' => 'purple',
        ]);
    }

    public function test_user_can_create_wallet_with_custom_icon(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(MoneyIndex::class)
            ->call('openWalletModal')
            ->assertSet('showWalletModal', true)
            ->set('walletName', 'Maybank Savings')
            ->set('walletType', 'bank')
            ->set('walletIcon', '🏦')
            ->set('walletStartingBalance', '1500.00')
            ->set('walletCurrency', 'MYR')
            ->call('saveWallet')
            ->assertSet('showWalletModal', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'name' => 'Maybank Savings',
            'type' => 'bank',
            'icon' => '🏦',
            'starting_balance' => '1500.00',
            'currency' => 'MYR',
        ]);
    }

    public function test_user_can_delete_wallet(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wallet = Wallet::query()->create([
            'user_id' => $user->id,
            'name' => 'Old E-Wallet',
            'type' => 'ewallet',
            'icon' => '📱',
            'starting_balance' => 50.00,
            'currency' => 'MYR',
        ]);

        Livewire::test(MoneyIndex::class)
            ->call('confirmDeleteWallet', $wallet->id)
            ->assertSet('showDeleteWalletModal', true)
            ->assertSet('deletingWalletId', $wallet->id)
            ->call('executeDeleteWallet')
            ->assertSet('showDeleteWalletModal', false);

        $this->assertDatabaseMissing('wallets', [
            'id' => $wallet->id,
        ]);
    }

    public function test_user_can_delete_custom_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Freelance Gig',
            'type' => 'income',
            'icon' => '💼',
            'color' => 'emerald',
        ]);

        Livewire::test(MoneyIndex::class)
            ->call('confirmDeleteCategory', $category->id)
            ->assertSet('showDeleteCategoryModal', true)
            ->assertSet('deletingCategoryId', $category->id)
            ->call('executeDeleteCategory')
            ->assertSet('showDeleteCategoryModal', false);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_can_update_and_delete_budget(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Groceries',
            'type' => 'expense',
            'icon' => '🛒',
            'color' => 'amber',
        ]);

        $budget = Budget::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month_year' => now()->format('Y-m'),
            'amount' => 500.00,
        ]);

        // Test Edit Budget
        Livewire::test(MoneyIndex::class)
            ->call('editBudget', $budget->id)
            ->assertSet('showBudgetModal', true)
            ->assertSet('editingBudgetId', $budget->id)
            ->set('budgetAmount', '750.00')
            ->call('saveBudget')
            ->assertSet('showBudgetModal', false);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => '750.00',
        ]);

        // Test Delete Budget
        Livewire::test(MoneyIndex::class)
            ->call('confirmDeleteBudget', $budget->id)
            ->assertSet('showDeleteBudgetModal', true)
            ->assertSet('deletingBudgetId', $budget->id)
            ->call('executeDeleteBudget')
            ->assertSet('showDeleteBudgetModal', false);

        $this->assertDatabaseMissing('budgets', [
            'id' => $budget->id,
        ]);
    }

    public function test_user_can_update_transaction(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wallet = Wallet::query()->create([
            'user_id' => $user->id,
            'name' => 'Cash in Hand',
            'type' => 'cash',
            'starting_balance' => 100.00,
            'currency' => 'MYR',
        ]);

        $category = Category::query()->create([
            'user_id' => $user->id,
            'name' => 'Food',
            'type' => 'expense',
            'icon' => '🍔',
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 25.00,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Lunch',
        ]);

        Livewire::test(MoneyIndex::class)
            ->call('editTransaction', $transaction->id)
            ->assertSet('showTransactionModal', true)
            ->assertSet('editingTransactionId', $transaction->id)
            ->set('transactionAmount', '35.50')
            ->set('transactionNotes', 'Lunch with team')
            ->call('saveTransaction')
            ->assertSet('showTransactionModal', false);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => '35.50',
            'notes' => 'Lunch with team',
        ]);
    }
}
