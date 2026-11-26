<?php

declare(strict_types=1);

use App\Actions\RegisterUserToEvent;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;

test('participants without a seat place do not take a seat', function () {
    $event = Event::factory()->create(['num_of_seats' => 4]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);
    EventUser::factory()->withoutSeatPlace()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    expect($event->seatsTaken())->toBe(2)
        ->and($event->seatsLeft())->toBe(2)
        ->and($event->hasSeatsLeft())->toBeTrue();
});

test('total participant count includes participants without a seat place', function () {
    $event = Event::factory()->create(['num_of_seats' => 4]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);
    EventUser::factory()->withoutSeatPlace()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);
    EventUser::factory()->waitingList()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    expect($event->participantsCount())->toBe(2);
});

test('period seats taken exclude participants without a seat place', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 2,
        'num_of_seats' => 2,
    ]);

    $period = $event->periods->get(0);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'event_period_id' => $period->id,
    ]);
    EventUser::factory()->withoutSeatPlace()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'event_period_id' => $period->id,
    ]);

    expect($period->seatsTaken())->toBe(1)
        ->and($period->seatsLeft())->toBe(1)
        ->and($period->hasSeatsLeft())->toBeTrue()
        ->and($event->seatsTaken($period->id))->toBe(1)
        ->and($event->seatsLeft())->toBe(3);
});

test('marking a participant without a seat place frees their seat for new registrations', function () {
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
    $user3 = User::factory()->create();

    $action = app(RegisterUserToEvent::class);

    $action->handle($user1, $event);
    $action->handle($user2, $event);

    $registration = EventUser::where('event_id', $event->id)
        ->where('user_id', $user1->id)
        ->first();

    $registration->update(['is_without_seat_place' => true]);

    $newRegistration = $action->handle($user3, $event);

    expect($newRegistration->in_waitinglist)->toBeFalse();
});
