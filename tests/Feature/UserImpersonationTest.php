<?php

use App\Livewire\Admin\UserImpersonationPage;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    // Add impersonate users permission as it might not be in the seeder yet if it's in a recent migration
    Permission::firstOrCreate(['name' => 'impersonate users']);
    Role::whereIn('name', ['super-admin', 'maintainer'])->get()->each(function ($role) {
        $role->givePermissionTo('impersonate users');
    });
});

test('unauthorized users cannot access impersonation page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.impersonation-page'))
        ->assertForbidden();
});

test('authorized users can access impersonation page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.impersonation-page'))
        ->assertSuccessful();
});

test('admin can impersonate a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $target = User::factory()->create(['name' => 'Target User']);

    Livewire::actingAs($admin)
        ->test(UserImpersonationPage::class)
        ->call('impersonate', $target->id)
        ->assertRedirect(route('impersonate', $target->id));
});

test('admin cannot impersonate another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $otherAdmin = User::factory()->create(['name' => 'Other Admin']);
    $otherAdmin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(UserImpersonationPage::class)
        ->call('impersonate', $otherAdmin->id)
        ->assertNoRedirect();
});

test('searching for users works', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    Livewire::actingAs($admin)
        ->test(UserImpersonationPage::class)
        ->set('search', 'John')
        ->assertViewHas('users', function ($users): bool {
            return $users->count() === 1 && $users->first()->name === 'John Doe';
        });
});
