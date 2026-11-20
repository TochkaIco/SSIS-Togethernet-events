<?php

declare(strict_types=1);

use App\Actions\UnregisterUserFromEvent;
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
        'seating_settings' => Event::makeSeatingSettings([4, 4, 4, 4, 4]),
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

    expect($registration->refresh()->seat_location)->toBe('A4')
        ->and($registration->seat_booked_at)->not->toBeNull();
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

test('a participant can change their booked seat to another free seat place', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    $component = Livewire::test(EventShow::class, ['event' => $event])
        ->call('selectSeat', 'A1-1')
        ->call('saveSeatBooking')
        ->assertHasNoErrors();
    expect($registration->refresh()->seat_location)->toBe('A1-1');

    $component->call('selectSeat', 'B2-1')
        ->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('B2-1')
        ->and($event->registrations()->where('seat_location', 'A1-1')->count())->toBe(0);
});

test('a participant cannot change their seat to a seat place another participant already holds', function () {
    $other = User::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $other->id, 'has_paid' => true, 'seat_location' => 'A1-1']);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true, 'seat_location' => 'B2-1']);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('selectSeat', 'A1-1')
        ->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('B2-1');
});

test('a participant with a booked seat sees a view seat button', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true, 'seat_location' => 'A1-1']);

    $this->get(route('event.show', $event))
        ->assertOk()
        ->assertSee('View Seat');
});

test('unregistering a user deletes their seat booking', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('bookSeat', 'A1-1')
        ->assertHasNoErrors();
    expect($registration->refresh()->seat_location)->toBe('A1-1');

    (new UnregisterUserFromEvent)->handle($user, $event);

    expect($event->registrations()->count())->toBe(0)
        ->and(EventUser::find($registration->id))->toBeNull();
});

test('a participant can book a seat by selecting it on the seating chart and sees their own seat labelled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    $component = Livewire::test(EventShow::class, ['event' => $event])
        ->call('selectSeat', 'A1-1')
        ->assertHasNoErrors()
        ->assertSet('selectedSeatLocation', 'A1-1');

    expect($registration->refresh()->seat_location)->toBeNull();

    $component->call('saveSeatBooking')
        ->assertHasNoErrors()
        ->assertSee(substr($user->email, 0, 6));

    expect($registration->refresh()->seat_location)->toBe('A1-1');
});

test('a participant cannot book a seat that another participant already holds and never sees their email', function () {
    $other = User::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $other->id, 'has_paid' => true, 'seat_location' => 'A1-1']);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    $component = Livewire::test(EventShow::class, ['event' => $event])
        ->call('openSeatBookingModal')
        ->assertDontSee(substr($other->email, 0, 6));

    $component->call('selectSeat', 'A1-1')->call('saveSeatBooking')->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('a participant can reselect their own already booked seat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true, 'seat_location' => 'A1-1']);

    Livewire::test(EventShow::class, ['event' => $event])
        ->call('selectSeat', 'A1-1')
        ->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('A1-1');
});

test('a paid participant sees the seating chart on the public event page and books a seat from it', function () {
    $user = User::factory()->create();
    $event = makeLanEventForBooking();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true]);

    $this->actingAs($user)
        ->get(route('event.show', $event))
        ->assertOk()
        ->assertSee('Save seat place')
        // The click handler must carry a quoted seat id, otherwise the inline JS is invalid and the click dies silently.
        ->assertSee("selectSeat('A1-1')", false);

    $component = Livewire::test(EventShow::class, ['event' => $event])
        ->call('openSeatBookingModal')
        ->assertHasNoErrors();

    $component->call('selectSeat', 'A3-1')
        ->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('A3-1');
});
