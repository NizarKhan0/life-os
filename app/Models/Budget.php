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
 * @property int $category_id
 * @property numeric-string|float $amount
 * @property string $month_year
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category $category
 */
#[Fillable([
    'user_id',
    'category_id',
    'amount',
    'month_year',
])]
class Budget extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate the total spent amount for this budget's category in its month_year.
     */
    public function spentAmount(): float
    {
        $parts = explode('-', $this->month_year);
        $year = $parts[0] !== '' ? (int) $parts[0] : (int) Carbon::now()->year;
        $month = isset($parts[1]) ? (int) $parts[1] : (int) Carbon::now()->month;

        $spent = Transaction::query()
            ->where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum('amount');

        return round((float) $spent, 2);
    }

    /**
     * Calculate budget usage percentage.
     */
    public function progressPercentage(): float
    {
        $limit = (float) $this->amount;

        if ($limit <= 0.0) {
            return 0.0;
        }

        $percentage = ($this->spentAmount() / $limit) * 100.0;

        return round(max(0.0, $percentage), 1);
    }
}
