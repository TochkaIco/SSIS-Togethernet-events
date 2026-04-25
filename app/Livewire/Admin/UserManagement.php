<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public string $createUserName = '';

    public string $createUserEmail = '';

    public string $createUserClass = '';

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $filterRole = '';

    public $editingUserId;

    public $editingUserName = '';

    public $userToDelete;

    public $userRoles = [];

    public $userPermissions = [];

    public $filterClassGroup = '';

    public function createUserModal(): void
    {
        $this->authorize('manage users');

        $this->modal('create-user')->show();
    }

    public function createUser(): void
    {
        $this->authorize('manage users');

        $this->validate([
            'createUserName' => ['required', 'string', 'min:3', 'max:255'],
            'createUserEmail' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'createUserClass' => ['required', 'string'],
        ]);

        User::create([
            'name' => $this->createUserName,
            'email' => $this->createUserEmail,
            'class' => $this->createUserClass,
        ]);

        $this->reset(['createUserName', 'createUserEmail', 'createUserClass']);

        $this->modal('create-user')->close();

        Flux::toast(__('User created!'), variant: 'success');
    }

    public function editAccess($userId): void
    {
        $this->authorize('manage users');

        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->editingUserName = $user->name;

        $this->userRoles = $user->roles->pluck('name')->toArray();
        $this->userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        $this->modal('edit-user-permissions')->show();
    }

    public function savePermissions(): void
    {
        $this->authorize('manage users');
        $user = User::findOrFail($this->editingUserId);

        // protectedRoles => ['admin', 'super-admin', 'maintainer']

        if (in_array('maintainer', $this->userRoles)) {
            if (! Auth::user()->hasRole(['maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Maintainers can grant Maintainer role.',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        } elseif (in_array('super-admin', $this->userRoles)) {
            if (! Auth::user()->hasAnyRole(['super-admin', 'maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Only Super-Admins or Maintainers can grant Super-Admin role.',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        } elseif (in_array('admin', $this->userRoles)) {
            if (! Auth::user()->hasAnyRole(['super-admin', 'maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Only Super-Admins or Maintainers can grant Admin role.',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        } elseif (in_array('delete users', $this->userPermissions)) {
            if (! Auth::user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Only Admins can grant permission to delete users..',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        } elseif (in_array('configure pages', $this->userPermissions)) {
            if (! Auth::user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Only Admins can grant permission to configure pages.',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        } elseif (in_array('dev', $this->userPermissions)) {
            if (! Auth::user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
                $this->modal('edit-user-permissions')->close();
                Flux::toast(
                    text: 'Only Admins can grant permission to view dev-related components.',
                    heading: 'Error',
                    variant: 'danger'
                );
                $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);

                return;
            }
        }
        $user->syncRoles($this->userRoles);
        $user->syncPermissions($this->userPermissions);

        $this->modal('edit-user-permissions')->close();
        Flux::toast(
            text: 'Permissions updated.',
            heading: 'Saved',
            variant: 'success'
        );
        $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);
    }

    public function confirmDelete($userId): void
    {
        // 1. Check permission immediately
        $this->authorize('delete users');

        $this->userToDelete = $userId;
        $this->modal('confirm-user-deletion')->show();
    }

    public function deleteUser(): void
    {
        $this->authorize('delete users');

        $user = User::findOrFail($this->userToDelete);

        if ($user->id === auth()->id()) {
            Flux::toast(text: 'You cannot delete yourself.', heading: 'Error', variant: 'danger');

            return;
        }

        $user->delete();

        $this->modal('confirm-user-deletion')->close();

        Flux::toast(text: 'The account has been removed.', heading: 'User Deleted', variant: 'success');
    }

    public function viewUserProfile($userId)
    {
        $this->authorize('manage users');

        return redirect()->route('admin.user.profile', $userId);
    }

    #[Layout('layouts.app', ['title' => 'Users'])]
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

        return view('livewire.admin.user-management', [
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
