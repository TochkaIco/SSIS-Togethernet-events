<?php

use App\Livewire\Admin\Events\Tabs\Participants;
use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('user can register for a specific period in a karaoke event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDay()->addHours(3),
        'display_starts_at' => now()->subDay(),
        'num_of_seats' => 20,
    ]);

    $periodToRegister = $event->periods->get(1); // Period 2

    Auth::login($user);

    Livewire::test(EventShow::class, ['event' => $event])
        ->set('period', $periodToRegister->id)
        ->call('registerUser', $event->id)
        ->assertStatus(200);

    $registration = EventUser::where('event_id', $event->id)->where('user_id', $user->id)->first();
    expect($registration->event_period_id)->toBe($periodToRegister->id);
});

test('admin can move participant to another period', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'event_starts_at' => now()->addDay(),
        'num_of_seats' => 20,
    ]);

    $user = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $period1 = $event->periods->get(0);
    $period3 = $event->periods->get(2);

    $registration = EventUser::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'event_period_id' => $period1->id,
    ]);

    Auth::login($admin);

    Livewire::test(Participants::class, ['event' => $event])
        ->set('participantPeriods.'.$registration->id, $period3->id)
        ->call('changePeriod', $registration->id)
        ->assertStatus(200);

    $registration->refresh();
    expect($registration->event_period_id)->toBe($period3->id);
});
