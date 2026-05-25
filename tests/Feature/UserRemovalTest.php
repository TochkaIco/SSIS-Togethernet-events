<?php

use App\Livewire\AcceptTerms;
use App\Models\Event;
use App\Models\EventKiosk;
use App\Models\EventKioskPurchase;
use App\Models\EventUser;
use App\Models\Feedback;
use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('user without activity is deleted when declining TOS', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => null,
    ]);

    Auth::login($user);

    Livewire::test(AcceptTerms::class)
        ->call('decline');

    expect(User::find($user->id))->toBeNull();
});

test('user with event registration is anonymized when declining TOS', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => null,
    ]);

    $event = Event::factory()->create();
    EventUser::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    Auth::login($user);

    Livewire::test(AcceptTerms::class)
        ->call('decline');

    $user->refresh();
    expect($user->isAnonymized())->toBeTrue();
    expect(User::find($user->id))->not->toBeNull();
});

test('user with kiosk purchase is anonymized when declining TOS', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => null,
    ]);

    $event = Event::factory()->create();
    $kiosk = new EventKiosk;
    $kiosk->event_id = $event->id;
    $kiosk->save();

    // Create a purchase where the user is the operator
    EventKioskPurchase::create([
        'operator_id' => $user->id,
        'kiosk_id' => $kiosk->id,
        'cost' => 100,
    ]);

    Auth::login($user);

    Livewire::test(AcceptTerms::class)
        ->call('decline');

    $user->refresh();
    expect($user->isAnonymized())->toBeTrue();
});

test('user with meeting attendance is anonymized when declining TOS', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => null,
    ]);

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

    Auth::login($user);

    Livewire::test(AcceptTerms::class)
        ->call('decline');

    $user->refresh();
    expect($user->isAnonymized())->toBeTrue();
});

test('user with feedback is anonymized when declining TOS', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => null,
    ]);

    Feedback::factory()->create([
        'user_id' => $user->id,
    ]);

    Auth::login($user);

    Livewire::test(AcceptTerms::class)
        ->call('decline');

    $user->refresh();
    expect($user->isAnonymized())->toBeTrue();
});
