<?php

use App\Livewire\Admin\AdminGlobalLogs;
use App\Models\GlobalLog;
use App\Models\User;
use Livewire\Livewire;

test('admin can clear old logs', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    // Create some old logs
    GlobalLog::factory()->count(5)->create(['created_at' => now()->subMonths(3)]);
    // Create some new logs
    GlobalLog::factory()->count(3)->create(['created_at' => now()]);

    Livewire::test(AdminGlobalLogs::class)
        ->set('monthsToKeep', 2)
        ->call('clearOldLogs');

    expect(GlobalLog::count())->toBe(4); // 3 new logs + 1 log for the clearing action itself
});

test('admin gets warning when clearing logs that do not exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    // No old logs created

    Livewire::test(AdminGlobalLogs::class)
        ->set('monthsToKeep', 2)
        ->call('clearOldLogs')
        ->assertHasNoErrors();

    expect(GlobalLog::count())->toBe(0); // No logs should be created, not even the "Cleared" log
});

test('non-admin cannot clear old logs', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    Livewire::test(AdminGlobalLogs::class)
        ->call('clearOldLogs')
        ->assertStatus(403);
});
