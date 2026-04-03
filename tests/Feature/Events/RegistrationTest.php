<?php

use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('user can register for an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    Livewire::actingAs($user)
        ->test(EventShow::class, ['event' => $event])
        ->assertSee('Register')
        ->call('registerUser', $event->id)
        ->assertSee('You are registered');

    expect($event->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

// test('user can unregister from an event', function () {
//    $user = User::factory()->create();
//    $event = Event::factory()->create([
//        'display_starts_at' => now()->subDay(),
//        'event_starts_at' => now()->addDay(),
//        'event_ends_at' => now()->addDays(2),
//    ]);
//
//    // Manually attach the user first
//    $event->users()->attach($user->id);
//
//    Livewire::actingAs($user)
//        ->test(EventShow::class, ['event' => $event])
//        ->assertSee('You are registered')
//        ->call('confirmUnregisterUser', $event->id)
//        ->call('unregisterUser')
//        ->assertSee('Register');
//
//    expect($event->users()->where('user_id', $user->id)->exists())->toBeFalse();
// });

test('registration button is not visible if event is not active', function () {
    $user = User::factory()->create();

    // Event has not started displaying yet
    $event = Event::factory()->create([
        'display_starts_at' => now()->addDay(),
        'event_starts_at' => now()->addDays(2),
        'event_ends_at' => now()->addDays(3),
    ]);

    Livewire::actingAs($user)
        ->test(EventShow::class, ['event' => $event])
        ->assertDontSee('Register');

    // Event has already ended
    $event2 = Event::factory()->create([
        'display_starts_at' => now()->subDays(5),
        'event_starts_at' => now()->subDays(4),
        'event_ends_at' => now()->subDays(3),
    ]);

    Livewire::actingAs($user)
        ->test(EventShow::class, ['event' => $event2])
        ->assertDontSee('Register');
});
