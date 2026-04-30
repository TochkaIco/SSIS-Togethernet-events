<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserImpersonationPage extends Component
{
    use WithPagination;

    public string $createUserName = '';

    public string $createUserEmail = '';

    public string $createUserClass = '';

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $filterRole = '';

    public $userRoles = [];

    public $userPermissions = [];

    public $filterClassGroup = '';

    public function impersonate($userId): void
    {
        $this->authorize('impersonate users');
        $user = User::findOrFail($userId);

        if ($user->canBeImpersonated()) {
            $this->redirect(route('impersonate', $user->id));
        } else {
            Flux::toast(
                text: __('This user cannot be impersonated.'),
                heading: __('Error'),
                variant: 'danger'
            );
        }
    }

    #[Layout('layouts.app', ['title' => 'User Impersonation'])]
    public function render(): View|Factory|\Illuminate\View\View
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterRole, function ($q) {
                $q->role($this->filterRole);
            })
            ->when($this->filterClassGroup, function ($q) {
                $q->where('class', 'like', $this->filterClassGroup.'%');
            })
            ->with('roles')
            ->paginate(12);

        return view('livewire.admin.user-impersonation-page', [
            'users' => $users,
            'allRoles' => Role::all(),
            'allPermissions' => Permission::all(),
            'allClassGroups' => [
                'Personal',
                'TE'.now()->subMonths(6)->format('y'),
                'TE'.now()->subMonths(6)->subYear()->format('y'),
                'TE'.now()->subMonths(6)->subYears(2)->format('y'),
            ],
        ]);
    }
}
