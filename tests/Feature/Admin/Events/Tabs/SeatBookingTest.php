<?php

declare(strict_types=1);

use App\EventType;
use App\Livewire\Admin\Events\Tabs\Participants;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('unauthorized users cannot open the seat booking modal, book a seat or remove a participant', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'is_without_seat_place' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->assertForbidden();

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'A1')
        ->assertForbidden();

    Livewire::test(Participants::class, ['event' => $event])
        ->call('removeParticipantFromEvent', $registration->id)
        ->assertForbidden();
});

test('admin can open the seat booking modal for a participant of a lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->assertHasNoErrors()
        ->assertSet('seatBookingRegistrationId', $registration->id);
});

test('admin cannot open the seat booking modal for a non lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id]);

    $component = Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->assertHasNoErrors();

    expect($component->instance()->seatBookingRegistrationId)->toBeNull();
});

test('a participant can only book a seat when the event is a lan and paid or free and the participant has a seat place', function (bool $isLan, bool $paidEntry, bool $hasPaid, bool $hasSeatPlace, bool $expected) {
    $event = Event::factory()->create([
        'event_type' => $isLan ? EventType::LAN->value : EventType::KARAOKE->value,
        'paid_entry' => $paidEntry,
    ]);
    $registration = EventUser::factory()->create([
        'event_id' => $event->id,
        'has_paid' => $hasPaid,
        'is_without_seat_place' => ! $hasSeatPlace,
    ]);

    expect($event->isLan())->toBe($isLan)
        ->and($event->isFree())->toBe(! $paidEntry)
        ->and($registration->canBookSeat())->toBe($expected);
})->with([
    [true, true, true, true, true],
    [true, true, false, true, false],
    [true, false, false, true, true],
    [true, false, true, true, true],
    [true, true, true, false, false],
    [true, false, true, false, false],
    [false, true, true, true, false],
    [false, true, false, true, false],
]);

test('a waiting list participant cannot book a seat', function () {
    $event = Event::factory()->create([
        'event_type' => EventType::LAN->value,
    ]);
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id, 'has_paid' => true]);

    expect($registration->canBookSeat())->toBeFalse();
});

test('admin can book a seat for a paid participant of a lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'A12')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('A12')
        ->and($registration->is_without_seat_place)->toBeFalse();
});

test('admin can book a seat for any participant of a free lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value, 'paid_entry' => false]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => false]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'B3')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('B3')
        ->and($registration->is_without_seat_place)->toBeFalse();
});

test('admin cannot book a seat for an unpaid participant of a paid lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => false]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'A12')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull()
        ->and($registration->is_without_seat_place)->toBeFalse();
});

test('admin cannot book a seat for a participant of a non lan event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'A12')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('admin cannot book a seat for a participant without a seat place', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('bookSeat', $registration->id, 'C7')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull()
        ->and($registration->is_without_seat_place)->toBeTrue();
});

test('admin cannot open the seat booking modal for a participant without a seat place', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id, 'has_paid' => true]);

    $component = Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->assertHasNoErrors();

    expect($component->instance()->seatBookingRegistrationId)->toBeNull();
});

test('admin can book a seat for a participant by selecting it on the seating chart', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    $component = Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->call('selectSeat', 'A1-1')
        ->assertHasNoErrors()
        ->assertSet('selectedSeatLocation', 'A1-1');

    expect($registration->refresh()->seat_location)->toBeNull();

    $component->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBe('A1-1');
});

test('selecting a seat only marks it as selected until the save button is pressed', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('selecting a seat does nothing when no seat booking modal is open', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('selectSeat', 'A1-1')
        ->assertHasNoErrors()
        ->assertSet('selectedSeatLocation', null);

    expect($registration->refresh()->seat_location)->toBeNull();
});

test('admin cannot book a seat another participant already holds', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $previousOccupant = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true, 'seat_location' => 'A1-1']);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true]);

    $component = Livewire::test(Participants::class, ['event' => $event])
        ->call('openSeatBookingModal', $registration->id)
        ->call('selectSeat', 'A1-1')
        ->assertHasNoErrors();

    $component->call('saveSeatBooking')
        ->assertHasNoErrors();

    expect($registration->refresh()->seat_location)->toBeNull()
        ->and($previousOccupant->refresh()->seat_location)->toBe('A1-1');
});

test('moving a participant to without a seat place removes their seat from the seating chart', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true, 'seat_location' => 'B2-1']);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('toggleIsWithoutSeatPlace', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->is_without_seat_place)->toBeTrue()
        ->and($registration->seat_location)->toBeNull();
});

test('restoring a seat place for a participant without a booked seat leaves the seat location unset', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true, 'seat_location' => 'B2-1']);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('toggleIsWithoutSeatPlace', $registration->id)
        ->call('toggleIsWithoutSeatPlace', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->is_without_seat_place)->toBeFalse()
        ->and($registration->seat_location)->toBeNull();
});

test('confirming a participant removal opens the confirmation modal for that participant', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id]);

    $component = Livewire::test(Participants::class, ['event' => $event])
        ->call('confirmRemoveParticipant', $registration->id)
        ->assertSet('registrationIdToRemove', $registration->id)
        ->assertSee('Remove participant from event?')
        ->assertSee($registration->user->name);

    $component->assertDispatched('modal-show', name: 'remove-participant-confirmation', scope: $component->instance()->getId());
});

test('admin can remove a participant without a seat place from the event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $periodId = $event->periods()->first()->id;
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id, 'event_period_id' => $periodId]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('removeParticipantFromEvent', $registration->id)
        ->assertHasNoErrors();

    expect(EventUser::find($registration->id))->toBeNull()
        ->and($event->registrations()->count())->toBe(0);
});

test('removing a participant from the event processes the waiting list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $periodId = $event->periods()->first()->id;

    EventUser::factory()->create(['event_id' => $event->id, 'event_period_id' => $periodId]);
    $registration = EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id, 'event_period_id' => $periodId]);
    $waiting = EventUser::factory()->waitingList()->create(['event_id' => $event->id, 'event_period_id' => $periodId]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('removeParticipantFromEvent', $registration->id)
        ->assertHasNoErrors();

    expect(EventUser::find($registration->id))->toBeNull()
        ->and($waiting->refresh()->in_waitinglist)->toBeFalse();
});

test('admin cannot remove a participant who has a seat place from the event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('removeParticipantFromEvent', $registration->id)
        ->assertHasNoErrors();

    expect(EventUser::find($registration->id))->not->toBeNull();
});
