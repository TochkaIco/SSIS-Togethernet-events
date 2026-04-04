<?php

use App\Actions\RegisterUserToEvent;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user registers as participant when seats are available', function () {
    $event = Event::factory()->create(['num_of_seats' => 5]);
    $user = User::factory()->create();

    (new RegisterUserToEvent)->handle($user, $event);

    expect(EventUser::where('event_id', $event->id)->where('user_id', $user->id)->first()->in_waitinglist)->toBeFalse();
});

test('user registers as waiter when no seats are available', function () {
    $event = Event::factory()->create(['num_of_seats' => 1]);
    $participant = User::factory()->create();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $participant->id, 'in_waitinglist' => false]);

    $waiter = User::factory()->create();
    (new RegisterUserToEvent)->handle($waiter, $event);

    expect(EventUser::where('event_id', $event->id)->where('user_id', $waiter->id)->first()->in_waitinglist)->toBeTrue();
});

test('waiter from previous event bumps participant from previous event', function () {
    $prevEvent = Event::factory()->create(['event_starts_at' => now()->subDays(10)]);
    $currentEvent = Event::factory()->create(['num_of_seats' => 1, 'event_starts_at' => now()->addDays(1)]);

    $userA = User::factory()->create(); // Was participant in prev event (Score 0)
    EventUser::factory()->create(['event_id' => $prevEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false]);

    $userB = User::factory()->create(); // Was waiter in prev event (Score 2)
    EventUser::factory()->create(['event_id' => $prevEvent->id, 'user_id' => $userB->id, 'in_waitinglist' => true]);

    // User A registers first for current event
    EventUser::factory()->create(['event_id' => $currentEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false, 'created_at' => now()->subMinute()]);

    // User B registers later
    (new RegisterUserToEvent)->handle($userB, $currentEvent);

    // User B should have bumped User A
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userB->id)->first()->in_waitinglist)->toBeFalse();
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userA->id)->first()->in_waitinglist)->toBeTrue();
});

test('newbie bumps participant from previous event', function () {
    $prevEvent = Event::factory()->create(['event_starts_at' => now()->subDays(10)]);
    $currentEvent = Event::factory()->create(['num_of_seats' => 1, 'event_starts_at' => now()->addDays(1)]);

    $userA = User::factory()->create(); // Was participant in prev event (Score 0)
    EventUser::factory()->create(['event_id' => $prevEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false]);

    $userB = User::factory()->create(); // Newbie (Score 1)

    // User A registers first
    EventUser::factory()->create(['event_id' => $currentEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false, 'created_at' => now()->subMinute()]);

    // User B registers
    (new RegisterUserToEvent)->handle($userB, $currentEvent);

    // User B should have bumped User A
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userB->id)->first()->in_waitinglist)->toBeFalse();
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userA->id)->first()->in_waitinglist)->toBeTrue();
});

test('waiter bumps newbie', function () {
    $prevEvent = Event::factory()->create(['event_starts_at' => now()->subDays(10)]);
    $currentEvent = Event::factory()->create(['num_of_seats' => 1, 'event_starts_at' => now()->addDays(1)]);

    $userA = User::factory()->create(); // Newbie (Score 1)

    $userB = User::factory()->create(); // Was waiter in prev event (Score 2)
    EventUser::factory()->create(['event_id' => $prevEvent->id, 'user_id' => $userB->id, 'in_waitinglist' => true]);

    // User A registers first
    EventUser::factory()->create(['event_id' => $currentEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false, 'created_at' => now()->subMinute()]);

    // User B registers
    (new RegisterUserToEvent)->handle($userB, $currentEvent);

    // User B should have bumped User A
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userB->id)->first()->in_waitinglist)->toBeFalse();
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userA->id)->first()->in_waitinglist)->toBeTrue();
});

test('same priority does not bump', function () {
    $currentEvent = Event::factory()->create(['num_of_seats' => 1]);
    $userA = User::factory()->create(); // Score 1
    $userB = User::factory()->create(); // Score 1

    // User A registers first
    EventUser::factory()->create(['event_id' => $currentEvent->id, 'user_id' => $userA->id, 'in_waitinglist' => false, 'created_at' => now()->subMinute()]);

    // User B registers
    (new RegisterUserToEvent)->handle($userB, $currentEvent);

    // User B should NOT have bumped User A
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userB->id)->first()->in_waitinglist)->toBeTrue();
    expect(EventUser::where('event_id', $currentEvent->id)->where('user_id', $userA->id)->first()->in_waitinglist)->toBeFalse();
});
