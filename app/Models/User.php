<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read EventUser $pivot
 */
#[Fillable(['name', 'email', 'locale', 'class', 'profile_picture', 'last_activity_at', 'inactivity_warning_sent_at', 'google_id', 'google_token', 'google_refresh_token', 'anonymized_at', 'tos_accepted_at', 'tos_warning_sent_at'])]
#[Hidden(['two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Impersonate, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'inactivity_warning_sent_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'tos_accepted_at' => 'datetime',
            'tos_warning_sent_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    public function anonymize(): void
    {
        $this->update([
            'name' => 'Anonymized',
            'email' => 'anonymized_'.$this->id.'@anonymized.com',
            'profile_picture' => null,
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
            'anonymized_at' => now(),
        ]);

        $this->syncRoles([]);
        $this->syncPermissions([]);

        $this->sessions()->delete();
    }

    public function scopeNotAnonymized($query)
    {
        return $query->whereNull('anonymized_at');
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
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

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class)->orderBy('last_activity', 'desc');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventUser::class);
    }

    public function kioskPurchases(): HasMany
    {
        return $this->hasMany(EventKioskPurchase::class, 'operator_id');
    }

    public function meetingAttendances(): HasMany
    {
        return $this->hasMany(MeetingAttendant::class, 'attendant_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_users')
            ->withPivot(['id', 'is_working', 'in_waitinglist', 'has_paid', 'has_arrived', 'event_period_id'])
            ->withTimestamps();
    }

    /**
     * Check if the user has any activity in the system.
     */
    public function hasActivity(): bool
    {
        if ($this->registrations()->exists()) {
            return true;
        }
        if ($this->kioskPurchases()->exists()) {
            return true;
        }
        if ($this->meetingAttendances()->where('has_attended', true)->exists()) {
            return true;
        }

        return $this->feedback()->exists();
    }

    /**
     * Remove the user by either deleting them (if no activity) or anonymizing them.
     */
    public function remove(): void
    {
        if ($this->hasActivity()) {
            $this->anonymize();

            return;
        }

        $this->sessions()->delete();
        $this->syncRoles([]);
        $this->syncPermissions([]);
        // Delete related meeting attendance records even if they didn't attend
        $this->meetingAttendances()->delete();
        $this->delete();
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

    public function validClasses(): array
    {
        return [
            'Personal',
            'Alumni',
            'TE'.now()->subMonths(6)->addDays(15)->format('y').'A',
            'TE'.now()->subMonths(6)->addDays(15)->format('y').'B',
            'TE'.now()->subMonths(6)->addDays(15)->format('y').'C',
            'TE'.now()->subMonths(6)->addDays(15)->format('y').'D',
            'TE'.now()->subMonths(6)->addDays(15)->subYear()->format('y').'A',
            'TE'.now()->subMonths(6)->addDays(15)->subYear()->format('y').'B',
            'TE'.now()->subMonths(6)->addDays(15)->subYear()->format('y').'C',
            'TE'.now()->subMonths(6)->addDays(15)->subYear()->format('y').'D',
            'TE'.now()->subMonths(6)->addDays(15)->subYears(2)->format('y').'A',
            'TE'.now()->subMonths(6)->addDays(15)->subYears(2)->format('y').'B',
            'TE'.now()->subMonths(6)->addDays(15)->subYears(2)->format('y').'C',
            'TE'.now()->subMonths(6)->addDays(15)->subYears(2)->format('y').'D',
        ];
    }

    public function canImpersonate(): bool
    {
        return $this->hasPermissionTo('impersonate users');
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasAnyRole('admin|super-admin|maintainer');
    }

    /**
     * Check if the user has an external email domain.
     */
    public function isExternal(): bool
    {
        $hd = config('services.google.hd');

        if (empty($hd)) {
            return false;
        }

        return ! str_ends_with($this->email, '@'.$hd);
    }
}
