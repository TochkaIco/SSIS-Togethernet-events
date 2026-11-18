<?php

declare(strict_types=1);

use App\EventType;
use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Livewire\Livewire;

function makeLanEventForBooking(array $overrides = []): Event
{
    return Event::factory()->create(array_merge([
        'event_type' => EventType::LAN->value,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ], $overrides));
}

test('a paid participant of a lan event can open the seat booking modal and book a seat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('openSeatBookingModal')
        ->assertHasNoErrors()
        ->assertSee('Reserve a seat place for yourself on the seating chart below.');

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('A4');
});

test('a participant of a free lan event can book a seat without paying', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking(['paid_entry' => false]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => false]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'B2')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('B2');
});

test('an unpaid participant of a paid lan event cannot book a seat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => false]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('a user who is not registered for the event cannot book a seat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('openSeatBookingModal')
        ->assertHasNoErrors();

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($event->registrations()->count())->toBe(0);
});

test('a participant without a seat place cannot book a seat from the public view', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('a waiting list participant cannot book a seat from the public view', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id, 'user_id' => $user->id]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('a participant cannot book a seat before the seat booking has started', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking(['seat_booking_starts_at' => now()->addDay()]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($event->seatBookingOpen())->toBeFalse()
        ->and($registration->refresh()->seat_location)->toBeNull();
});

test('a participant of a non lan event cannot book a seat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('a participant cannot book a seat after the event has finished', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking([
        'event_starts_at' => now()->subDays(2),
        'event_ends_at' => now()->subDay(),
    ]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('guests are redirected to login when trying to book a seat', function () {
    $event = makeLanEventForBooking();

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('openSeatBookingModal')
        ->assertRedirect(route('login'));

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A4')
        ->assertRedirect(route('login'));
});

test('seat booking is open when the start date is set to the past or not set at all', function () {
    $openEvent = Event::factory()->create(['seat_booking_starts_at' => now()->subHour()]);
    $nullEvent = Event::factory()->create(['seat_booking_starts_at' => null]);

    expect($openEvent->seatBookingOpen())->toBeTrue()
        ->and($nullEvent->seatBookingOpen())->toBeTrue();
});

test('seat booking is not open before the start date', function () {
    $event = Event::factory()->create(['seat_booking_starts_at' => now()->addHour()]);

    expect($event->seatBookingOpen())->toBeFalse();
});
