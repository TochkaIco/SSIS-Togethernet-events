<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('it updates last_activity_at on requests', function () {
    $user = User::factory()->create(['last_activity_at' => null]);
    Auth::login($user);

    $this->get('/');

    $user->refresh();
    expect($user->last_activity_at)->not->toBeNull();
});

test('it does not update last_activity_at too frequently', function () {
    $initialTime = now()->subMinutes(2);
    $user = User::factory()->create(['last_activity_at' => $initialTime]);
    Auth::login($user);

    $this->get('/');

    $user->refresh();
    expect($user->last_activity_at->toDateTimeString())->toBe($initialTime->toDateTimeString());

    // Travel to 6 minutes later
    $this->travelTo(now()->addMinutes(6));

    $this->get('/');

    $user->refresh();
    expect($user->last_activity_at->toDateTimeString())->not->toBe($initialTime->toDateTimeString());
});
