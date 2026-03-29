<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserProfile extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->authorize('manage users');
        $this->user = $user->load(['roles', 'permissions']);
    }

    public function render(): View
    {
        $lastActivity = $this->user->sessions()->first()?->last_activity;

        return view('livewire.admin.user-profile', [
            'lastActivity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
        ]);
    }
}
