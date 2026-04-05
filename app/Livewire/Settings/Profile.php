<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $google_id = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        if (Auth::user()->google_id) {
            $this->google_id = Auth::user()->google_id;
        } else {
            $this->google_id = '';
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    //    public function updateProfileInformation(): void
    //    {
    //        $user = Auth::user();
    //
    //        $validated = $this->validate($this->profileRules($user->id));
    //
    //        $user->fill($validated);
    //
    //        if ($user->isDirty('email')) {
    //            $user->email_verified_at = null;
    //        }
    //
    //        $user->save();
    //
    //        $this->dispatch('profile-updated', name: $user->name);
    //    }

    public function pullPictureFromGoogle()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $user->update([
            'profile_picture' => null,
        ]);
        Auth::logout();

        return redirect()->route('login');
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return Auth::user()->hasVerifiedEmail();
    }
}
