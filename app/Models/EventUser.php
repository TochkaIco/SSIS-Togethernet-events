<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property int $event_period_id
 * @property bool $in_waitinglist
 * @property bool $has_paid
 * @property bool $has_arrived
 * @property bool $is_working
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Table(name: 'event_users')]
#[Fillable([
    'user_id',
    'event_id',
    'event_period_id',
    'in_waitinglist',
    'has_paid',
    'has_arrived',
    'is_working',
])]
class EventUser extends Model
{
    /** @use HasFactory<EventUserFactory> */
    use HasFactory;

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'in_waitinglist' => 'boolean',
            'has_paid' => 'boolean',
            'has_arrived' => 'boolean',
            'event_id' => 'integer',
            'user_id' => 'integer',
            'event_period_id' => 'integer',
            'is_working' => 'boolean',
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
     * @return BelongsTo<EventPeriod, $this>
     */
    public function eventPeriod(): BelongsTo
    {
        return $this->belongsTo(EventPeriod::class, 'event_period_id');
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @param  Builder<EventUser>  $query
     */
    public function scopeParticipants(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where('in_waitinglist', false)
                ->orWhereNull('in_waitinglist');
        });
    }

    /**
     * @param  Builder<EventUser>  $query
     */
    public function scopeWaitingList(Builder $query): void
    {
        $query->where('in_waitinglist', true);
    }
}
