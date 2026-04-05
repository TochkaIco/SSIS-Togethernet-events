<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UserProfile extends Component
{
    public User $user;

    #[Validate('required|string|min:3|max:255')]
    public string $name = '';

    public function mount(User $user): void
    {
        $this->authorize('manage users');
        $this->user = $user->load(['roles', 'permissions']);
        $this->name = $user->name;
    }

    public function changeUserName(): void
    {
        $this->authorize('manage users');

        $this->user->update([
            'name' => $this->name,
        ]);

        Flux::toast(text: __('Name updated.'), variant: 'success');
    }

    public function render(): View
    {
        $lastActivity = $this->user->sessions()->first()?->last_activity;

        return view('livewire.admin.user-profile', [
            'lastActivity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
        ]);
    }
}
