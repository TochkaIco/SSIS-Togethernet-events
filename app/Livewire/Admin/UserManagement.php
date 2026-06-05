<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\GlobalLog;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClassGroup(): void
    {
        $this->resetPage();
    }

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

        $user = User::create([
            'name' => $this->createUserName,
            'email' => $this->createUserEmail,
            'class' => $this->createUserClass,
        ]);

        GlobalLog::log('User Created', 'user', ['user_id' => $user->id]);

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

        $currentRoles = $user->roles->pluck('name')->toArray();
        $currentPerms = $user->getDirectPermissions()->pluck('name')->toArray();

        $addedRoles = array_diff($this->userRoles, $currentRoles);
        $removedRoles = array_diff($currentRoles, $this->userRoles);
        $addedPerms = array_diff($this->userPermissions, $currentPerms);
        $removedPerms = array_diff($currentPerms, $this->userPermissions);

        $isHighLevel = Auth::user()->hasAnyRole(['super-admin', 'maintainer']);
        $isAdmin = Auth::user()->hasRole('admin') || $isHighLevel;

        // Check for high-level role changes (Admin, Super-Admin, Maintainer)
        $protectedRoles = ['admin', 'super-admin', 'maintainer'];
        $modifyingProtected = array_intersect($protectedRoles, array_merge($addedRoles, $removedRoles));

        if ($modifyingProtected !== [] && ! $isHighLevel) {
            $this->failWithToast(__('Only Super-Admins or Maintainers can modify administrative roles.'));

            return;
        }

        // Check for permission changes
        if (($addedPerms !== [] || $removedPerms !== []) && ! $isAdmin) {
            $this->failWithToast(__('Only Admins can grant or revoke permissions.'));

            return;
        }

        $user->syncRoles($this->userRoles);
        $user->syncPermissions($this->userPermissions);

        $details = [
            'target_user_id' => $user->id,
            'added_roles' => array_values($addedRoles),
            'removed_roles' => array_values($removedRoles),
            'added_permissions' => array_values($addedPerms),
            'removed_permissions' => array_values($removedPerms),
        ];

        GlobalLog::log('User Permissions Updated', 'user', array_filter($details));

        $this->modal('edit-user-permissions')->close();
        Flux::toast(text: __('Permissions updated.'), heading: __('Saved'), variant: 'success');
        $this->reset(['editingUserId', 'editingUserName', 'userRoles', 'userPermissions']);
    }

    /**
     * Helper to reduce boilerplate code
     */
    private function failWithToast(string $message): void
    {
        $this->modal('edit-user-permissions')->close();
        Flux::toast(text: $message, heading: __('Error'), variant: 'danger');
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
            Flux::toast(text: __('You cannot delete yourself.'), heading: __('Error'), variant: 'danger');

            return;
        }

        GlobalLog::log('User Deleted/Anonymized', 'user', ['target_user_id' => $user->id]);

        $user->remove();

        $this->modal('confirm-user-deletion')->close();

        Flux::toast(text: __('The account has been removed.'), heading: __('User Deleted'), variant: 'success');
    }

    public function viewUserProfile($userId)
    {
        $this->authorize('manage users');

        return redirect()->route('admin.user.profile', $userId);
    }

    #[Layout('layouts.app')]
    public function render(): View|Factory|\Illuminate\View\View
    {
        $users = User::query()
            ->notAnonymized()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterRole, function ($q) {
                $q->whereHas('roles', function ($q) {
                    $q->where('name', $this->filterRole);
                });
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
            'validClasses' => (new User)->validClasses(),
            'allClassGroups' => [
                'Personal',
                'Alumni',
                'TE'.now()->subMonths(7)->format('y'),
                'TE'.now()->subMonths(7)->subYear()->format('y'),
                'TE'.now()->subMonths(7)->subYears(2)->format('y'),
            ],
        ])->title(__('Users'));
    }
}
