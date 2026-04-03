<?php

use App\Livewire\Admin\Events\ParticipantProfile;
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

test('unauthorized users cannot view participant profile', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $participant = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ParticipantProfile::class, [
            'event' => $event,
            'userId' => $participant->id,
        ])
        ->assertForbidden();
});

test('authorized users can view participant profile and see worker status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $event = Event::factory()->create();
    $worker = User::factory()->create();
    $attendee = User::factory()->create();

    EventUser::create(['event_id' => $event->id, 'user_id' => $worker->id, 'is_working' => true]);
    EventUser::create(['event_id' => $event->id, 'user_id' => $attendee->id, 'is_working' => false]);

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, [
            'event' => $event,
            'userId' => $worker->id,
        ])
        ->assertOk()
        ->assertSee(__('Worker'))
        ->assertDontSee(__('Attendee'));

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, [
            'event' => $event,
            'userId' => $attendee->id,
        ])
        ->assertOk()
        ->assertSee(__('Attendee'))
        ->assertDontSee(__('Worker'));
});

test('it loads roles and permissions for the participant', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('tog-member');

    $event = Event::factory()->create();

    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, [
            'event' => $event,
            'userId' => $user->id,
        ])
        ->assertOk()
        ->assertSee('tog-member');
});
