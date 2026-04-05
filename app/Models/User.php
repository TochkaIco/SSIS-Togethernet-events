<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read EventUser $pivot
 */
#[Fillable(['name', 'email', 'locale', 'class', 'profile_picture', 'google_id', 'google_token', 'google_refresh_token'])]
#[Hidden(['two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class)->orderBy('last_activity', 'desc');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_users')
            ->using(EventUser::class)
            ->withPivot(['is_working', 'in_waitinglist', 'has_paid', 'has_arrived'])
            ->withTimestamps();
    }

    /**
     * Get the registration priority score for a given event.
     * Higher score means higher priority.
     * 2: Was on the waiting list in the previous event.
     * 1: Did not register for the previous event.
     * 0: Was a participant in the previous event.
     */
    public function registrationPriorityFor(Event $event): int
    {
        $previousEvent = $event->previous();

        if (! $previousEvent instanceof Event) {
            return 1;
        }

        $registration = EventUser::where('event_id', $previousEvent->id)
            ->where('user_id', $this->id)
            ->first();

        if (! $registration) {
            return 1;
        }

        return $registration->in_waitinglist ? 2 : 0;
    }
}
