<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $event_id
 * @property string $type
 * @property int|null $number
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Event $event
 */
#[Fillable([
    'event_id',
    'type',
    'number',
    'starts_at',
    'ends_at',
])]
class EventPeriod extends Model
{
    use HasFactory;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<EventUser, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventUser::class, 'event_period_id');
    }

    public function seatsTaken(): int
    {
        if ($this->type === 'break') {
            return 0;
        }

        return $this->registrations()->participants()->count();
    }

    public function seatsLeft(): int
    {
        if ($this->type === 'break') {
            return 0;
        }

        return max(0, $this->event->num_of_seats - $this->seatsTaken());
    }

    public function hasSeatsLeft(): bool
    {
        if ($this->type === 'break') {
            return false;
        }

        return $this->seatsLeft() > 0;
    }

    /**
     * Register a user for this period.
     */
    public function register(User $user): EventUser
    {
        // 1. If seats are available, register as participant
        if ($this->hasSeatsLeft()) {
            return EventUser::create([
                'event_id' => $this->event_id,
                'event_period_id' => $this->id,
                'user_id' => $user->id,
                'in_waitinglist' => false,
            ]);
        }

        // 2. Join waiting list (no more bumping)
        return EventUser::create([
            'event_id' => $this->event_id,
            'event_period_id' => $this->id,
            'user_id' => $user->id,
            'in_waitinglist' => true,
        ]);
    }

    /**
     * Promote the next person on the waiting list for this period.
     */
    public function promoteNext(): void
    {
        if (! AppConfig::get('automated_waiting_list_move', true)) {
            return;
        }

        if (! $this->hasSeatsLeft()) {
            return;
        }

        $nextInLine = $this->registrations()
            ->waitingList()
            ->orderBy('updated_at')
            ->first();

        if ($nextInLine) {
            $nextInLine->update(['in_waitinglist' => false]);

            // If more seats are available, recurse
            $this->promoteNext();
        }
    }

    protected function label(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->type === 'break') {
                    $duration = $this->starts_at->diffInMinutes($this->ends_at);

                    return __('Break')." ({$duration} min)";
                }

                return $this->starts_at->format('H:i').' - '.$this->ends_at->format('H:i');
            },
        );
    }
}
