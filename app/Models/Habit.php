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
 * @property string $frequency
 * @property int $target_per_day
 * @property string|null $color
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, HabitLog> $logs
 */
#[Fillable([
    'user_id',
    'name',
    'frequency',
    'target_per_day',
    'color',
    'is_active',
])]
class Habit extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_per_day' => 'integer',
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
     * @return HasMany<HabitLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    /**
     * Determine whether this habit has met its target for today.
     */
    public function isCompletedToday(): bool
    {
        $todayStr = Carbon::today()->toDateString();
        $target = max(1, $this->target_per_day);

        $logs = $this->relationLoaded('logs')
            ? $this->logs
            : $this->logs()->get();

        $todayLog = $logs->first(
            fn (HabitLog $log): bool => $log->logged_date->toDateString() === $todayStr
        );

        return $todayLog !== null && $todayLog->count >= $target;
    }

    /**
     * Calculate consecutive days completed without break using Carbon.
     */
    public function currentStreak(): int
    {
        $target = max(1, $this->target_per_day);

        $logs = $this->relationLoaded('logs')
            ? $this->logs
            : $this->logs()->get();

        /** @var array<string, bool> $completedDates */
        $completedDates = [];

        foreach ($logs as $log) {
            if ($log->count >= $target) {
                $completedDates[$log->logged_date->toDateString()] = true;
            }
        }

        if ($completedDates === []) {
            return 0;
        }

        $cursor = Carbon::today();

        if (! isset($completedDates[$cursor->toDateString()])) {
            $cursor = Carbon::yesterday();

            if (! isset($completedDates[$cursor->toDateString()])) {
                return 0;
            }
        }

        $streak = 0;

        while (isset($completedDates[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->copy()->subDay();
        }

        return $streak;
    }
}
