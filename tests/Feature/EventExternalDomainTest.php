<?php

use App\Actions\RegisterUserToEvent;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.google.hd', 'ssis.nu');
});

test('user with internal domain can register even if external domains are prohibited', function () {
    $event = Event::factory()->create([
        'allow_external_domains' => false,
        'num_of_seats' => 10,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user = User::factory()->create(['email' => 'test@ssis.nu']);
    $action = app(RegisterUserToEvent::class);

    $registration = $action->handle($user, $event);

    expect($registration)->not->toBeNull();
});

test('user with external domain cannot register if external domains are prohibited', function () {
    $event = Event::factory()->create([
        'allow_external_domains' => false,
        'num_of_seats' => 10,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user = User::factory()->create(['email' => 'test@gmail.com']);
    $action = app(RegisterUserToEvent::class);

    $registration = $action->handle($user, $event);

    expect($registration)->toBeNull();
});

test('user with external domain can register if external domains are allowed', function () {
    $event = Event::factory()->create([
        'allow_external_domains' => true,
        'num_of_seats' => 10,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    $user = User::factory()->create(['email' => 'test@gmail.com']);
    $action = app(RegisterUserToEvent::class);

    $registration = $action->handle($user, $event);

    expect($registration)->not->toBeNull();
});
