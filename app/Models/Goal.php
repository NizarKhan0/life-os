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
 * @property string $title
 * @property string|null $description
 * @property string $category
 * @property Carbon|null $target_date
 * @property numeric-string|float|null $target_value
 * @property numeric-string|float $current_value
 * @property string|null $unit
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, Milestone> $milestones
 */
#[Fillable([
    'user_id',
    'title',
    'description',
    'category',
    'target_date',
    'target_value',
    'current_value',
    'unit',
    'status',
])]
class Goal extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
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
     * @return HasMany<Milestone, $this>
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Calculate progress percentage based on target_value or completed milestones.
     */
    public function progressPercentage(): float
    {
        $targetValue = (float) ($this->target_value ?? 0);

        if ($targetValue > 0.0) {
            $currentValue = (float) $this->current_value;
            $percentage = ($currentValue / $targetValue) * 100.0;

            return round(min(100.0, max(0.0, $percentage)), 1);
        }

        $milestones = $this->relationLoaded('milestones')
            ? $this->milestones
            : $this->milestones()->get();

        $totalMilestones = $milestones->count();

        if ($totalMilestones > 0) {
            $completedMilestones = $milestones->where('is_completed', true)->count();

            return round(($completedMilestones / $totalMilestones) * 100.0, 1);
        }

        return $this->status === 'completed' ? 100.0 : 0.0;
    }

    /**
     * Calculate remaining days until target_date using Carbon.
     */
    public function daysRemaining(): ?int
    {
        if ($this->target_date === null) {
            return null;
        }

        return (int) Carbon::now()->startOfDay()->diffInDays($this->target_date->copy()->startOfDay(), false);
    }
}
