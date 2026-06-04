<?php

use App\EventType;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;

test('homepage shows QR code when user has active QR tag registration', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_ends_at' => now()->addDay(),
    ]);
    $registration = EventUser::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'qr_tag_token' => 'test-token',
        'qr_tag_tagged_at' => null,
        'is_disabled' => false,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertSee($event->title)
        ->assertSee('<svg', false);
});

test('homepage does not show QR code when user is tagged', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_ends_at' => now()->addDay(),
    ]);
    $registration = EventUser::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'qr_tag_token' => 'test-token',
        'qr_tag_tagged_at' => now(),
        'is_disabled' => false,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertDontSee('Your QR-Tag is active!');
});

test('homepage does not show QR code when game has not started', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_ends_at' => now()->addDay(),
    ]);
    $registration = EventUser::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'qr_tag_token' => null,
        'qr_tag_tagged_at' => null,
        'is_disabled' => false,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertDontSee('Your QR-Tag is active!');
});

test('homepage does not show QR code when event has finished', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_ends_at' => now()->subHour(),
    ]);
    $registration = EventUser::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'qr_tag_token' => 'test-token',
        'qr_tag_tagged_at' => null,
        'is_disabled' => false,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertDontSee('Your QR-Tag is active!');
});
