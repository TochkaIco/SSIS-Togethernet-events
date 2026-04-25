<?php

use App\Livewire\Admin\Meetings\Show;
use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'take attendance']);
    Role::firstOrCreate(['name' => 'tog-member']);
    Role::firstOrCreate(['name' => 'admin'])->givePermissionTo('take attendance');
});

test('admin can toggle attendance by calling the component method', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $meeting = Meeting::factory()->create();
    $member = User::factory()->create();
    $member->assignRole('tog-member');

    Livewire::test(Show::class, ['meeting' => $meeting])
        ->call('toggleAttendance', $member->id);

    expect(MeetingAttendant::where('meeting_id', $meeting->id)
        ->where('attendant_id', $member->id)
        ->where('has_attended', true)
        ->exists())->toBeTrue();

    Livewire::test(Show::class, ['meeting' => $meeting])
        ->call('toggleAttendance', $member->id);

    expect(MeetingAttendant::where('meeting_id', $meeting->id)
        ->where('attendant_id', $member->id)
        ->where('has_attended', false)
        ->exists())->toBeTrue();
});

test('unauthorized user cannot toggle attendance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();
    $member = User::factory()->create();
    $member->assignRole('tog-member');

    Livewire::test(Show::class, ['meeting' => $meeting])
        ->call('toggleAttendance', $member->id)
        ->assertForbidden();
});
