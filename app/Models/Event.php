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
    'allow_external_domains',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $casts = [
        'event_type' => EventType::class,
        'links' => AsArrayObject::class,
        'one_hour_periods' => 'boolean',
        'one_hour_periods_number' => 'integer',
        'interval_length' => 'integer',
        'display_starts_at' => 'datetime',
        'event_starts_at' => 'datetime',
        'event_ends_at' => 'datetime',
        'allow_external_domains' => 'boolean',
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
     * @return HasMany<QrTagLog, $this>
     */
    public function qrTagLogs(): HasMany
    {
        return $this->hasMany(QrTagLog::class)->latest();
    }

    public function qrTagActiveParticipantsCount(): int
    {
        return $this->registrations()
            ->whereNull('qr_tag_tagged_at')
            ->count();
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

    public function canRegister(): bool
    {
        if ($this->display_starts_at > now() || $this->event_ends_at < now()) {
            return false;
        }

        // QR-Tag specific: cannot register once started
        return ! ($this->event_type === EventType::QR_TAG && $this->event_starts_at <= now());
    }

    public function canUnregister(): bool
    {
        return $this->event_starts_at > now();
    }

    /**
     * Check if the given user is allowed to register for this event.
     */
    public function allowsUser(User $user): bool
    {
        if ($this->allow_external_domains) {
            return true;
        }

        return ! $user->isExternal();
    }

    public function isQrTagGameStarted(): bool
    {
        return $this->registrations()->whereNotNull('qr_tag_token')->exists();
    }

    public function hasParticipants(): bool
    {
        return $this->registrations()->exists();
    }

    public function canDelete(): bool
    {
        if ($this->created_at > now()->subMinutes(30)) {
            return true;
        }

        return ! $this->hasParticipants();
    }

    public function canEditCriticalFields(): bool
    {
        return ! $this->hasParticipants();
    }

    /**
     * @return Collection<int, EventUser>
     */
    public function qrTagLeaderboard(): Collection
    {
        return $this->participants()
            ->with('user')
            ->whereNull('qr_tag_tagged_at')
            ->where('is_disabled', false)
            ->orderByDesc('qr_tag_count')
            ->where('qr_tag_count', '>', 0)
            ->take(5)
            ->get();
    }
}
