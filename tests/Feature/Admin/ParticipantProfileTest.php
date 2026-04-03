<?php

use App\Livewire\Admin\Events\ParticipantProfile;
use App\Models\Event;
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
        ->test(ParticipantProfile::class, ['event' => $event, 'user' => $participant])
        ->assertForbidden();
});

test('authorized users can view participant profile and see worker status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $event = Event::factory()->create();
    $worker = User::factory()->create();
    $attendee = User::factory()->create();

    $event->users()->attach($worker, ['is_working' => true]);
    $event->users()->attach($attendee, ['is_working' => false]);

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, ['event' => $event, 'user' => $worker])
        ->assertOk()
        ->assertSee(__('Worker'))
        ->assertDontSee(__('Attendee'))
        ->assertSee(__('Back to Participants'));

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, ['event' => $event, 'user' => $attendee])
        ->assertOk()
        ->assertSee(__('Attendee'))
        ->assertDontSee(__('Worker'));
});

test('it loads roles and permissions for the participant', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $event = Event::factory()->create();
    $participant = User::factory()->create();
    $participant->assignRole('tog-member');

    $event->users()->attach($participant, ['is_working' => false]);

    Livewire::actingAs($admin)
        ->test(ParticipantProfile::class, ['event' => $event, 'user' => $participant])
        ->assertOk()
        ->assertSee('tog-member')
        ->assertSee('view articles');
});
