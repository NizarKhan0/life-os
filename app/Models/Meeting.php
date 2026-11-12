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
 * @property string|null $location_or_link
 * @property Carbon $start_time
 * @property Carbon|null $end_time
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, MeetingReminder> $reminders
 */
#[Fillable([
    'user_id',
    'title',
    'description',
    'location_or_link',
    'start_time',
    'end_time',
    'status',
])]
class Meeting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
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
     * @return HasMany<MeetingReminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(MeetingReminder::class);
    }

    /**
     * Determine whether the meeting is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture() && $this->status === 'scheduled';
    }
}
