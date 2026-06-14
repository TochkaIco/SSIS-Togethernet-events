<?php

use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Livewire\Livewire;

test('admin can view user management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk();
});

test('clicking on a user name in management redirects to profile', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('viewUserProfile', $user->id)
        ->assertRedirect(route('admin.user.profile', $user->id));
});

test('admin can view user profile with correct information', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    $user->assignRole('tog-member');

    $this->actingAs($admin)
        ->get(route('admin.user.profile', $user->id))
        ->assertOk()
        ->assertSee('John Doe')
        ->assertSee('john@example.com')
        ->assertSee('tog-member')
        ->assertSee('view articles');
});

test('non-admin cannot view user profile', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.user.profile', $otherUser->id))
        ->assertForbidden();
});

test('admin cannot delete super-admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('confirmDelete', $superAdmin->id)
        ->assertSet('userToDelete', null);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('userToDelete', $superAdmin->id)
        ->call('deleteUser');

    expect(User::find($superAdmin->id))->not->toBeNull();
});

test('super-admin can delete admin', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($superAdmin)
        ->test(UserManagement::class)
        ->set('userToDelete', $admin->id)
        ->call('deleteUser');

    expect(User::find($admin->id))->toBeNull();
});

test('maintainer can delete super-admin', function () {
    $maintainer = User::factory()->create();
    $maintainer->assignRole('maintainer');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Livewire::actingAs($maintainer)
        ->test(UserManagement::class)
        ->set('userToDelete', $superAdmin->id)
        ->call('deleteUser');

    expect(User::find($superAdmin->id))->toBeNull();
});

test('super-admin cannot delete maintainer', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $maintainer = User::factory()->create();
    $maintainer->assignRole('maintainer');

    Livewire::actingAs($superAdmin)
        ->test(UserManagement::class)
        ->call('confirmDelete', $maintainer->id)
        ->assertSet('userToDelete', null);

    Livewire::actingAs($superAdmin)
        ->test(UserManagement::class)
        ->set('userToDelete', $maintainer->id)
        ->call('deleteUser');

    expect(User::find($maintainer->id))->not->toBeNull();
});
