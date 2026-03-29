<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public string $confirmation = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $user = Auth::user();

        if ($user->google_id) {
            $this->validate([
                'confirmation' => ['required', 'string', 'in:DELETE'],
            ], [
                'confirmation.in' => __('Please enter "DELETE" to confirm your account deletion.'),
            ]);
        } else {
            $this->validate([
                'password' => $this->currentPasswordRules(),
            ]);
        }

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
