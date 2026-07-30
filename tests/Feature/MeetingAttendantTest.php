<?php

declare(strict_types=1);

use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;

test('meeting attendant can be created and is associated with meeting and user', function () {
    $meeting = Meeting::factory()->create();
    $user = User::factory()->create();

    $attendant = MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user->id,
        'has_attended' => true,
    ]);

    expect($attendant->meeting)->toBeInstanceOf(Meeting::class)
        ->and($attendant->meeting->id)->toBe($meeting->id)
        ->and($attendant->user)->toBeInstanceOf(User::class)
        ->and($attendant->user->id)->toBe($user->id)
        ->and($attendant->has_attended)->toBeTrue();
});

test('has_attended is cast to a boolean', function () {
    $meeting = Meeting::factory()->create();
    $user = User::factory()->create();

    $attendant1 = MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user->id,
        'has_attended' => 1,
    ]);

    $attendant2 = MeetingAttendant::create([
        'meeting_id' => $meeting->id,
        'attendant_id' => $user->id,
        'has_attended' => 0,
    ]);

    expect($attendant1->has_attended)->toBeTrue()
        ->and($attendant2->has_attended)->toBeFalse();
});
