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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
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
     * @return BelongsToMany<User, $this, EventUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_users')
            ->using(EventUser::class)
            ->withPivot(['is_working', 'in_waitinglist', 'has_paid', 'has_arrived', 'period'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this, EventUser>
     */
    public function participants(): BelongsToMany
    {
        return $this->users()->where(function ($query) {
            $query->where('event_users.in_waitinglist', false)
                ->orWhereNull('event_users.in_waitinglist');
        });
    }

    /**
     * @return BelongsToMany<User, $this, EventUser>
     */
    public function waitingList(): BelongsToMany
    {
        return $this->users()->where('event_users.in_waitinglist', true);
    }

    public function previous(): ?Event
    {
        return self::where('event_starts_at', '<', $this->event_starts_at)
            ->orderBy('event_starts_at', 'desc')
            ->first();
    }

    public function seatsTaken(?int $period = null): int
    {
        return $this->participants()
            ->when($period !== null, fn ($query) => $query->where('event_users.period', $period))
            ->count();
    }

    public function seatsLeft(?int $period = null): int
    {
        if ($this->one_hour_periods && $period === null) {
            // For period-based events, showing "total" seats left is ambiguous.
            // We'll return the total capacity minus total participants,
            // or maybe just the capacity of one period if that's more intuitive.
            // Let's stick to total for now if that's what's shown in the UI.
            return max(0, ($this->num_of_seats * ($this->one_hour_periods_number ?? 1)) - $this->seatsTaken());
        }

        return max(0, $this->num_of_seats - $this->seatsTaken($period));
    }

    public function hasSeatsLeft(?int $period = null): bool
    {
        if ($this->one_hour_periods && $period === null) {
            // If no period specified, check if ANY period has seats left.
            return $this->eventPeriods()
                ->where('type', 'period')
                ->contains(fn ($p) => $this->hasSeatsLeft($p->number));
        }

        return $this->seatsLeft($period) > 0;
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
        if (! $this->one_hour_periods || ! $this->one_hour_periods_number) {
            return collect();
        }

        $schedule = collect();
        $currentStart = Carbon::parse($this->event_starts_at);
        $numPeriods = $this->one_hour_periods_number ?? 1;
        $interval = $this->interval_length ?? 0;

        for ($i = 1; $i <= $numPeriods; $i++) {
            $periodEnd = $currentStart->copy()->addHour();

            $schedule->push((object) [
                'type' => 'period',
                'number' => $i,
                'start' => $currentStart->copy(),
                'end' => $periodEnd,
                'label' => $currentStart->format('H:i').' - '.$periodEnd->format('H:i'),
            ]);

            // 2. Create a Break
            if ($interval > 0 && $i < $numPeriods) {
                $breakStart = $periodEnd->copy();
                $breakEnd = $breakStart->copy()->addMinutes($interval);

                $schedule->push((object) [
                    'type' => 'break',
                    'start' => $breakStart,
                    'end' => $breakEnd,
                    'label' => __('Break')." ({$interval} min)",
                ]);

                // Set the start of the next period to the end of this break
                $currentStart = $breakEnd->copy();
            } else {
                // No interval, just move to the end of the current period
                $currentStart = $periodEnd->copy();
            }
        }

        return $schedule;
    }
}
