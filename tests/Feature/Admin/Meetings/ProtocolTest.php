<?php

declare(strict_types=1);

use App\Jobs\BackupMeetingToGoogleDrive;
use App\Livewire\Admin\Meetings\Protocol;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'tog-member']);
});

test('it renders protocol edit page', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Protocol::class, ['meeting' => $meeting])
        ->assertStatus(200)
        ->assertSee($meeting->title);
});

test('it validates dates when saving protocol', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create([
        'meeting_starts_at' => now(),
    ]);

    Livewire::test(Protocol::class, ['meeting' => $meeting])
        ->set('meeting_starts_at', '2026-07-30T17:00')
        ->set('meeting_ends_at', '2026-07-30T16:00') // Ends before starts
        ->call('save')
        ->assertHasErrors(['meeting_ends_at']);
});

test('unauthorized users cannot save protocol', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Protocol::class, ['meeting' => $meeting])
        ->call('save')
        ->assertForbidden();
});

test('tog members can save protocol and dispatch backup job', function () {
    Bus::fake();

    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Protocol::class, ['meeting' => $meeting])
        ->set('title', 'Updated Meeting Title')
        ->set('description', '<p>New description</p>')
        ->set('meeting_starts_at', '2026-07-30T17:00')
        ->set('meeting_ends_at', '2026-07-30T18:00')
        ->call('save')
        ->assertHasNoErrors();

    $meeting->refresh();
    expect($meeting->title)->toEqual('Updated Meeting Title')
        ->and($meeting->description)->toEqual('<p>New description</p>')
        ->and($meeting->meeting_starts_at->format('Y-m-d H:i'))->toEqual('2026-07-30 17:00')
        ->and($meeting->meeting_ends_at->format('Y-m-d H:i'))->toEqual('2026-07-30 18:00');

    Bus::assertDispatched(BackupMeetingToGoogleDrive::class);
});
