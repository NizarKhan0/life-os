<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $meeting_id
 * @property int $remind_before_minutes
 * @property string $channel
 * @property bool $is_sent
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Meeting $meeting
 */
#[Fillable([
    'meeting_id',
    'remind_before_minutes',
    'channel',
    'is_sent',
    'sent_at',
])]
class MeetingReminder extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'remind_before_minutes' => 'integer',
            'is_sent' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Meeting, $this>
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Determine whether this reminder has entered its trigger window.
     */
    public function isDue(?Carbon $now = null): bool
    {
        if ($this->is_sent) {
            return false;
        }

        $currentTime = $now ?? Carbon::now();
        $meeting = $this->meeting;

        if ($meeting->status !== 'scheduled') {
            return false;
        }

        $triggerTime = $meeting->start_time->copy()->subMinutes($this->remind_before_minutes);

        return $currentTime->greaterThanOrEqualTo($triggerTime)
            && $currentTime->lessThanOrEqualTo($meeting->start_time->copy()->addMinutes(15));
    }
}
