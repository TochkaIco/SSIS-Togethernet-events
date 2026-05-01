<?php

use App\Livewire\Events\EventShow;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('registered period is highlighted on event show page', function () {
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'num_of_seats' => 10,
    ]);

    // Register user for period 2
    $period2 = $event->periods->where('number', 2)->first();
    EventUser::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'event_period_id' => $period2->id,
        'in_waitinglist' => false,
    ]);

    Livewire::actingAs($user)
        ->test(EventShow::class, ['event' => $event])
        ->assertSee(__('Your Time'))
        ->assertSee(__('Period').' 2')
        ->assertSee('ring-2 ring-orange-400');
});

test('registered waiting list period is highlighted differently', function () {
    $user = User::factory()->create();

    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
        'one_hour_periods' => true,
        'one_hour_periods_number' => 3,
        'num_of_seats' => 10,
    ]);

    // Register user for period 2 on waiting list
    $period2 = $event->periods->where('number', 2)->first();
    EventUser::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'event_period_id' => $period2->id,
        'in_waitinglist' => true,
    ]);

    Livewire::actingAs($user)
        ->test(EventShow::class, ['event' => $event])
        ->assertSee(__('Your Time (Waiting List)'))
        ->assertSee(__('Period').' 2')
        ->assertSee('ring-2 ring-orange-400');
});
