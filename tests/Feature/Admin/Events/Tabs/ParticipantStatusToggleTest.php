<?php

declare(strict_types=1);

use App\Livewire\Admin\Events\Tabs\Participants;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

it('records the paid_at timestamp when a participant is marked as paid', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => false]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('togglePaid', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->has_paid)->toBeTrue()
        ->and($registration->paid_at)->not->toBeNull();
});

it('clears the paid_at timestamp when the paid status is toggled back off', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_paid' => true, 'paid_at' => now()->subHour()]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('togglePaid', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->has_paid)->toBeFalse()
        ->and($registration->paid_at)->toBeNull();
});

it('records the arrived_at timestamp when a participant is marked as arrived', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_arrived' => false]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('toggleArrived', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->has_arrived)->toBeTrue()
        ->and($registration->arrived_at)->not->toBeNull();
});

it('clears the arrived_at timestamp when the arrival status is toggled back off', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id, 'has_arrived' => true, 'arrived_at' => now()->subHour()]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('toggleArrived', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->has_arrived)->toBeFalse()
        ->and($registration->arrived_at)->toBeNull();
});

it('unauthorized users cannot toggle the paid or arrival status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->create(['event_id' => $event->id]);

    Livewire::test(Participants::class, ['event' => $event])
        ->call('togglePaid', $registration->id)
        ->assertForbidden();

    Livewire::test(Participants::class, ['event' => $event])
        ->call('toggleArrived', $registration->id)
        ->assertForbidden();
});
