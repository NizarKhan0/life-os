<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $category
 * @property numeric-string|float $amount
 * @property string $billing_cycle
 * @property Carbon $next_billing_date
 * @property int|null $wallet_id
 * @property bool $auto_renew
 * @property bool $is_active
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Wallet|null $wallet
 */
#[Fillable([
    'user_id',
    'name',
    'category',
    'amount',
    'billing_cycle',
    'next_billing_date',
    'wallet_id',
    'auto_renew',
    'is_active',
    'notes',
])]
class Subscription extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_billing_date' => 'date',
            'auto_renew' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Calculate remaining days until next_billing_date using Carbon.
     */
    public function daysUntilDue(): int
    {
        return (int) Carbon::now()->startOfDay()->diffInDays($this->next_billing_date->copy()->startOfDay(), false);
    }

    /**
     * Determine whether this subscription is due within the given number of days (or today).
     */
    public function isDueSoon(int $days = 3): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $remaining = $this->daysUntilDue();

        return $remaining >= 0 && $remaining <= $days;
    }

    /**
     * Calculate normalized monthly commitment cost.
     */
    public function monthlyEquivalentAmount(): float
    {
        $amount = (float) $this->amount;

        if ($this->billing_cycle === 'yearly') {
            return round($amount / 12.0, 2);
        }

        return round($amount, 2);
    }
}
