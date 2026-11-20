<?php

declare(strict_types=1);

use App\EventType;
use App\Livewire\Admin\Events\Tabs\ViewDetails;
use App\Models\Event;
use App\Models\EventKiosk;
use App\Models\EventKioskPurchase;
use App\Models\EventUser;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

it('calculates statistics correctly', function () {
    $event = Event::factory()->create(['num_of_seats' => 10]);

    $user1 = User::factory()->create(['class' => 'TE22']);
    $user2 = User::factory()->create(['class' => 'TE22']);
    $user3 = User::factory()->create(['class' => 'EE23']);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user1->id,
        'has_arrived' => true,
        'in_waitinglist' => false,
    ]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user2->id,
        'has_arrived' => false,
        'in_waitinglist' => false,
    ]);

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user3->id,
        'has_arrived' => true,
        'in_waitinglist' => false,
    ]);

    // Waiting list user - should not be counted in stats
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'in_waitinglist' => true,
    ]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    $stats = $component->get('stats');

    expect($stats['registrations'])->toBe(3);
    expect($stats['registration_timeline']['times'])->toHaveCount(3);
    expect($stats['registration_timeline']['event_start'])->toBe(Carbon::parse($event->event_starts_at)->getTimestamp() * 1000);
    expect($stats['registration_timeline']['event_created'])->toBe($event->created_at->getTimestamp() * 1000);

    $classDist = $stats['class_distribution'];
    expect($classDist['labels'])->toContain('TE22', 'EE23');
    expect($classDist['data'])->toContain(2, 1);

    expect($stats['seat_counts'])->toBe(['with_seat' => 3, 'without_seat' => 0]);
});

it('counts participants with and without seats', function () {
    $event = Event::factory()->create();

    EventUser::factory()->create(['event_id' => $event->id]);
    EventUser::factory()->create(['event_id' => $event->id]);
    EventUser::factory()->withoutSeatPlace()->create(['event_id' => $event->id]);
    // Waiting list participants are not counted
    EventUser::factory()->waitingList()->create(['event_id' => $event->id]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    expect($component->get('stats')['seat_counts'])->toBe(['with_seat' => 2, 'without_seat' => 1]);
});

it('calculates earnings from entry fees and kiosk purchases', function () {
    $event = Event::factory()->create(['entry_fee' => 100]);

    $paidUser = User::factory()->create();
    $unpaidUser = User::factory()->create();

    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $paidUser->id, 'has_paid' => true, 'in_waitinglist' => false]);
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $unpaidUser->id, 'has_paid' => false, 'in_waitinglist' => false]);

    $kiosk = new EventKiosk;
    $kiosk->event_id = $event->id;
    $kiosk->save();

    $purchase = new EventKioskPurchase;
    $purchase->forceFill([
        'kiosk_id' => $kiosk->id,
        'operator_id' => $paidUser->id,
        'cost' => 35,
    ])->save();

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    $earnings = $component->get('stats')['earnings'];

    expect($earnings['entry_fee'])->toBe(100.0)
        ->and($earnings['kiosk'])->toBe(35.0)
        ->and($earnings['total'])->toBe(135.0);
});

it('calculates entry fee earnings as zero for free events', function () {
    $event = Event::factory()->create(['paid_entry' => false, 'entry_fee' => 0]);

    $user = User::factory()->create();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'has_paid' => true, 'in_waitinglist' => false]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    expect($component->get('stats')['earnings']['entry_fee'])->toBe(0.0);
});

it('builds status timelines for payments, arrivals and seat bookings', function () {
    $event = Event::factory()->create(['event_type' => EventType::LAN->value]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user1->id, 'has_paid' => true, 'paid_at' => now()->subHours(3), 'in_waitinglist' => false]);
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user2->id, 'has_paid' => true, 'has_arrived' => true, 'paid_at' => now()->subHours(1), 'arrived_at' => now()->subMinutes(30), 'in_waitinglist' => false]);
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user3->id, 'seat_location' => 'A1-1', 'seat_booked_at' => now()->subHours(2), 'in_waitinglist' => false]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    $stats = $component->get('stats');

    $ids = collect($stats['status_timelines'])->map(fn (array $timeline) => $timeline['id'])->toArray();
    expect($ids)->toBe(['paymentTimelineChart', 'arrivalTimelineChart', 'seatBookingTimelineChart']);

    expect($stats['status_timelines'][0]['times'])->toHaveCount(2)
        ->and($stats['status_timelines'][0]['times'][0])->toBeLessThan($stats['status_timelines'][0]['times'][1])
        ->and($stats['status_timelines'][1]['times'])->toHaveCount(1)
        ->and($stats['status_timelines'][2]['times'])->toHaveCount(1);
});

it('only includes the seat booking timeline for lan events', function () {
    $event = Event::factory()->create(['event_type' => EventType::KARAOKE->value]);

    $component = Livewire::test(ViewDetails::class, ['event' => $event]);

    $ids = collect($component->get('stats')['status_timelines'])->map(fn (array $timeline) => $timeline['id'])->toArray();

    expect($ids)->toBe(['paymentTimelineChart', 'arrivalTimelineChart']);
});

it('renders the disabled seating chart for lan events with a seating layout', function () {
    $event = Event::factory()->create([
        'event_type' => EventType::LAN->value,
        'seating_settings' => Event::makeSeatingSettings([2, 2, 0, 0, 0]),
    ]);

    $user = User::factory()->create();
    EventUser::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'in_waitinglist' => false, 'seat_location' => 'A1-1', 'seat_booked_at' => now()->subHour()]);

    Livewire::test(ViewDetails::class, ['event' => $event])
        ->assertSee('Seating Chart')
        ->assertSee('paymentTimelineChart', escape: false)
        ->assertSee('seatBookingTimelineChart', escape: false);
});
