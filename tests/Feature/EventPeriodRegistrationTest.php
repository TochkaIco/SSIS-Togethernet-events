<?php

use App\Livewire\Admin\Events\Tabs\Participants;
use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin'); // Assuming this role exists based on migrations
});

test('user can register for a specific period in a karaoke event', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDay()->addHours(3),
        'display_starts_at' => now()->subDay(),
        'num_of_seats' => 20,
    ]);

    Auth::login($this->user);

    Livewire::test(EventShow::class, ['event' => $event])
        ->set('period', 2)
        ->call('registerUser', $event->id)
        ->assertStatus(200);

    $registration = EventUser::where('event_id', $event->id)->where('user_id', $this->user->id)->first();
    expect($registration->period)->toBe(2);
});

test('admin can move participant to another period', function () {
    $event = Event::factory()->create([
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'event_starts_at' => now()->addDay(),
        'num_of_seats' => 20,
    ]);

    $event->users()->attach($this->user, ['period' => 1]);

    Auth::login($this->admin);

    Livewire::test(Participants::class, ['event' => $event])
        ->set('participantPeriods.'.$this->user->id, 3)
        ->call('changePeriod', $this->user->id)
        ->assertStatus(200);

    $registration = EventUser::where('event_id', $event->id)->where('user_id', $this->user->id)->first();
    expect($registration->period)->toBe(3);
});
