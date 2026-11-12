<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string|null $icon
 * @property numeric-string|float $starting_balance
 * @property string $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, Transaction> $transactions
 * @property-read Collection<int, Transaction> $incomingTransfers
 */
#[Fillable([
    'user_id',
    'name',
    'type',
    'icon',
    'starting_balance',
    'currency',
])]
class Wallet extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starting_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the display icon for this wallet with fallback to type default.
     */
    public function displayIcon(): string
    {
        if (! empty($this->icon)) {
            return $this->icon;
        }

        return match ($this->type) {
            'bank' => '🏦',
            'ewallet' => '📱',
            default => '💵',
        };
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'target_wallet_id');
    }

    /**
     * Calculate current balance dynamically from starting_balance and transactions.
     */
    public function currentBalance(): float
    {
        $starting = (float) $this->starting_balance;

        $transactions = $this->relationLoaded('transactions')
            ? $this->transactions
            : $this->transactions()->get();

        $incomingTransfers = $this->relationLoaded('incomingTransfers')
            ? $this->incomingTransfers
            : $this->incomingTransfers()->where('type', 'transfer')->get();

        $income = (float) $transactions->where('type', 'income')->sum(
            fn (Transaction $t): float => (float) $t->amount
        );

        $expense = (float) $transactions->where('type', 'expense')->sum(
            fn (Transaction $t): float => (float) $t->amount
        );

        $outgoingTransfer = (float) $transactions->where('type', 'transfer')->sum(
            fn (Transaction $t): float => (float) $t->amount
        );

        $incomingTransfer = (float) $incomingTransfers->where('type', 'transfer')->sum(
            fn (Transaction $t): float => (float) $t->amount
        );

        return round($starting + $income - $expense - $outgoingTransfer + $incomingTransfer, 2);
    }
}
