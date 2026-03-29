<?php

use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolesAndPermissionsSeeder::class);
});

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
