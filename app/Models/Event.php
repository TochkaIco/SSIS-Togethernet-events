<?php

declare(strict_types=1);

namespace App\Models;

use App\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @method static create(mixed[] $data)
 */
#[Fillable([
    'title',
    'description',
    'event_type',
    'image_path',
    'num_of_seats',
    'paid_entry',
    'entry_fee',
    'one_hour_periods',
    'interval_length',
    'one_hour_periods_number',
    'display_starts_at',
    'event_starts_at',
    'event_ends_at',
    'links',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $casts = [
        'event_type' => EventType::class,
        'links' => AsArrayObject::class,
        'display_starts_at' => 'datetime',
        'event_starts_at' => 'datetime',
        'event_ends_at' => 'datetime',
    ];

    protected function formattedDescription(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes) => str($attributes['description'])->markdown());
    }

    /**
     * @return HasMany<EventUser, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventUser::class);
    }

    /**
     * @return HasMany<EventPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(EventPeriod::class)->orderBy('starts_at');
    }

    /**
     * @return HasMany<EventUser, $this>
     */
    public function participants(): HasMany
    {
        return $this->registrations()->where(function ($query) {
            $query->where('in_waitinglist', false)
                ->orWhereNull('in_waitinglist');
        });
    }

    /**
     * @return HasMany<EventUser, $this>
     */
    public function waitingList(): HasMany
    {
        return $this->registrations()->where('in_waitinglist', true);
    }

    public function previous(): ?Event
    {
        return self::where('event_starts_at', '<', $this->event_starts_at)
            ->orderBy('event_starts_at', 'desc')
            ->first();
    }

    public function seatsTaken(?int $periodId = null): int
    {
        if ($periodId !== null) {
            $period = $this->periods()->find($periodId);

            return $period ? $period->seatsTaken() : 0;
        }

        return $this->participants()->count();
    }

    public function seatsLeft(?int $periodId = null): int
    {
        if ($periodId !== null) {
            $period = $this->periods()->find($periodId);

            return $period ? $period->seatsLeft() : 0;
        }

        if ($this->one_hour_periods) {
            return $this->periods()->where('type', 'period')->get()->sum(fn (EventPeriod $p) => $p->seatsLeft());
        }

        return max(0, $this->num_of_seats - $this->seatsTaken());
    }

    public function hasSeatsLeft(?int $periodId = null): bool
    {
        if ($periodId !== null) {
            $period = $this->periods()->find($periodId);

            return $period && $period->hasSeatsLeft();
        }

        if ($this->one_hour_periods) {
            return $this->periods()->where('type', 'period')->get()->contains(fn (EventPeriod $p) => $p->hasSeatsLeft());
        }

        return $this->seatsLeft() > 0;
    }

    /**
     * @return HasOne<EventKiosk, $this>
     */
    public function kiosk(): HasOne
    {
        return $this->hasOne(EventKiosk::class, 'event_id');
    }

    public function eventPeriods(): Collection
    {
        if (! $this->one_hour_periods) {
            return collect();
        }

        return $this->periods;
    }
}
