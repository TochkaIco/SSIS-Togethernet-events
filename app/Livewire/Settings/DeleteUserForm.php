<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $user = Auth::user();

        $this->validate([
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ], [
            'confirmation.in' => __('Please enter "DELETE" to confirm your account deletion.'),
        ]);

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
