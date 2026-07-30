<?php

declare(strict_types=1);

use App\Livewire\Admin\Events\Tabs\WaitingList;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('unauthorized users cannot perform waiting list actions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id]);

    Livewire::test(WaitingList::class, ['event' => $event])
        ->call('moveToParticipants', $registration->id)
        ->assertForbidden();

    Livewire::test(WaitingList::class, ['event' => $event])
        ->call('viewUserProfile', $registration->id)
        ->assertForbidden();
});

test('admin can move a user from waiting list to participants list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create([
        'event_ends_at' => now()->addDay(),
    ]);
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id]);

    Livewire::test(WaitingList::class, ['event' => $event])
        ->call('moveToParticipants', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->in_waitinglist)->toBeFalse();
});

test('admin cannot move a user to participants list if event has finished', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create([
        'event_ends_at' => now()->subDay(),
    ]);
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id]);

    Livewire::test(WaitingList::class, ['event' => $event])
        ->call('moveToParticipants', $registration->id)
        ->assertHasNoErrors();

    expect($registration->refresh()->in_waitinglist)->toBeTrue();
});

test('admin can view waiting list participant profile', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create();
    $registration = EventUser::factory()->waitingList()->create(['event_id' => $event->id]);

    Livewire::test(WaitingList::class, ['event' => $event])
        ->call('viewUserProfile', $registration->id)
        ->assertRedirect(route('admin.event.participant.profile', [$event, $registration->user_id]));
});
