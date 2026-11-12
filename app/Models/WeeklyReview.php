<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property CarbonInterface $week_start_date
 * @property string|null $wins
 * @property string|null $challenges
 * @property string|null $improvements
 * @property array<string, int|float>|null $metrics_snapshot
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'week_start_date',
    'wins',
    'challenges',
    'improvements',
    'metrics_snapshot',
])]
class WeeklyReview extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start_date' => 'date',
            'metrics_snapshot' => 'array',
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
     * Get the end date (Sunday) of the reviewed week.
     */
    public function weekEndDate(): CarbonInterface
    {
        return $this->week_start_date->copy()->endOfWeek();
    }

    /**
     * Determine whether all 3 reflection questions have been answered.
     */
    public function isComplete(): bool
    {
        return filled($this->wins) && filled($this->challenges) && filled($this->improvements);
    }
}
