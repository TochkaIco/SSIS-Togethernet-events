<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class UserProfile extends Component
{
    public User $user;

    #[Validate('required|string|min:3|max:255')]
    public string $name = '';

    public string $class = '';

    public string $selectedClass = '';

    public function mount(User $user): void
    {
        $this->authorize('manage users');
        $this->user = $user->load(['roles', 'permissions']);
        $this->name = $user->name;
        $this->class = $user->class ?? 'Unknown';
        $this->selectedClass = $this->class;
    }

    public function changeUserName(): void
    {
        $this->authorize('manage users');

        $this->user->update([
            'name' => $this->name,
        ]);

        Flux::toast(text: __('Name updated.'), variant: 'success');
    }

    public function updatedSelectedClass($value): void
    {
        $this->changeUserClass($value);
    }

    public function changeUserClass($class): void
    {
        $this->authorize('manage users');

        $this->user->update([
            'class' => $class,
        ]);

        Flux::toast(text: __('Class updated.'), variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.admin.user-profile', [
            'lastActivity' => $this->user->last_activity_at,
        ]);
    }
}
