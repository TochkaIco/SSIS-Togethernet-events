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
            ->withPivot(['is_working', 'in_waitinglist', 'has_paid', 'has_arrived'])
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
        return $this->users()->wherePivot('in_waitinglist', true);
    }

    public function previous(): ?Event
    {
        return self::where('event_starts_at', '<', $this->event_starts_at)
            ->orderBy('event_starts_at', 'desc')
            ->first();
    }

    public function seatsTaken(): int
    {
        return $this->participants()->count();
    }

    public function seatsLeft(): int
    {
        return max(0, $this->num_of_seats - $this->seatsTaken());
    }

    public function hasSeatsLeft(): bool
    {
        return $this->seatsLeft() > 0;
    }

    /**
     * @return HasOne<EventKiosk, $this>
     */
    public function kiosk(): HasOne
    {
        return $this->hasOne(EventKiosk::class, 'event_id');
    }
}
