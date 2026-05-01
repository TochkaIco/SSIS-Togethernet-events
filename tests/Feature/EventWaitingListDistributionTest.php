<?php

use App\Actions\RegisterUserToEvent;
use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('flexible registration assigns the first available period', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 2,
        'num_of_seats' => 1,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $action = app(RegisterUserToEvent::class);

    $period1 = $event->periods->get(0);
    $period2 = $event->periods->get(1);

    // User 1 registers for period 1
    $action->handle($user1, $event, $period1->id);

    // User 2 registers with no period (flexible)
    $action->handle($user2, $event);

    $reg2 = EventUser::where('event_id', $event->id)->where('user_id', $user2->id)->first();
    expect($reg2->event_period_id)->toBe($period2->id);
    expect($reg2->in_waitinglist)->toBeFalse();
});

test('flexible registration puts user on waiting list if all periods are full', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 1,
        'num_of_seats' => 1,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $action = app(RegisterUserToEvent::class);

    $period1 = $event->periods->get(0);

    // User 1 registers for period 1 (fills it)
    $action->handle($user1, $event, $period1->id);

    // User 2 registers with no period (flexible)
    $action->handle($user2, $event);

    $reg2 = EventUser::where('event_id', $event->id)->where('user_id', $user2->id)->first();
    expect($reg2->in_waitinglist)->toBeTrue();
});

test('unregistration promotes the next person on the waiting list for that period', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 1,
        'num_of_seats' => 1,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $action = app(RegisterUserToEvent::class);

    $period1 = $event->periods->get(0);

    // User 1 fills the seat
    $action->handle($user1, $event, $period1->id);

    // User 2 goes to waiting list
    $action->handle($user2, $event, $period1->id);

    expect(EventUser::where('event_id', $event->id)->where('user_id', $user2->id)->first()->in_waitinglist)->toBeTrue();

    // User 1 unregisters
    Auth::login($user1);
    Livewire::test(EventShow::class, ['event' => $event])
        ->set('eventIdToUnregister', $event->id)
        ->call('unregisterUser');

    expect(EventUser::where('event_id', $event->id)->where('user_id', $user2->id)->first()->in_waitinglist)->toBeFalse();
});
