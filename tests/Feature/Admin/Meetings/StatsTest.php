<?php

use App\Livewire\Admin\Meetings\Show;
use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('calculates meeting statistics correctly', function () {
    $role = Role::firstOrCreate(['name' => 'tog-member']);

    $meeting = Meeting::factory()->create();

    // Create 4 members
    $user1 = User::factory()->create(['class' => 'TE22']);
    $user1->assignRole($role);

    $user2 = User::factory()->create(['class' => 'TE22']);
    $user2->assignRole($role);

    $user3 = User::factory()->create(['class' => 'EE23']);
    $user3->assignRole($role);

    $user4 = User::factory()->create(['class' => 'EE23']);
    $user4->assignRole($role);

    // 3 attended, 1 absent
    MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user1->id,
        'has_attended' => true,
    ]);

    MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user2->id,
        'has_attended' => true,
    ]);

    MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user3->id,
        'has_attended' => true,
    ]);

    MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user4->id,
        'has_attended' => false,
    ]);

    $admin = User::factory()->create();
    $admin->givePermissionTo('take attendance');

    $component = Livewire::actingAs($admin)->test(Show::class, ['meeting' => $meeting]);

    $stats = $component->get('stats');

    expect($stats['total_members'])->toBe(4);
    expect($stats['attended'])->toBe(3);
    expect($stats['absent'])->toBe(1);
    expect($stats['attendance_rate'])->toBe(75);

    $classDist = $stats['class_distribution'];
    expect($classDist['labels'])->toContain('TE22', 'EE23');
    expect($classDist['data'])->toContain(2, 1); // 2 from TE22 attended, 1 from EE23 attended
});
