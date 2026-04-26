<?php

use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('tog members can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));
    $response->assertOk();
});

test('dashboard works with meetings and attendance', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $meeting = Meeting::create([
        'title' => 'Test Meeting',
        'meeting_starts_at' => now(),
        'meeting_ends_at' => now()->addHour(),
    ]);

    MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user->id,
        'has_attended' => true,
    ]);

    $response = $this->get(route('admin.dashboard'));
    $response->assertOk();
});
