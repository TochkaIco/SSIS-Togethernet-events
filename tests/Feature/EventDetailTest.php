<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public event detail page can be rendered', function () {
    $startsAt = Carbon\Carbon::parse('2026-08-28 19:15:00');
    $endsAt = Carbon\Carbon::parse('2026-08-28 21:45:00');

    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => $startsAt,
        'event_ends_at' => $endsAt,
    ]);

    $response = $this->get(route('event.show', $event));

    $response->assertOk();
    $response->assertSee($event->title);
    $response->assertSee($startsAt->format('M j, Y, H:i'));
    $response->assertSee($endsAt->format('M j, Y, H:i'));
    $response->assertDontSee($startsAt->format('M j, Y, h:i'));
    $response->assertDontSee($endsAt->format('M j, Y, h:i'));
});

test('admin event detail page can be rendered as admin', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $startsAt = Carbon\Carbon::parse('2026-08-28 19:15:00');
    $endsAt = Carbon\Carbon::parse('2026-08-28 21:45:00');

    $event = Event::factory()->create([
        'event_starts_at' => $startsAt,
        'event_ends_at' => $endsAt,
    ]);

    $response = $this->actingAs($user)->get(route('admin.event.show', $event));

    $response->assertOk();
    $response->assertSee($event->title);
    $response->assertSee($startsAt->format('M j, Y, H:i'));
    $response->assertSee($endsAt->format('M j, Y, H:i'));
});

test('admin event detail page cannot be rendered as guest', function () {
    $event = Event::factory()->create();

    $response = $this->get(route('admin.event.show', $event));

    $response->assertRedirect(route('login'));
});

test('admin event detail page can handle links', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $event = Event::factory()->create([
        'links' => ['https://google.com', 'https://github.com'],
    ]);

    $response = $this->actingAs($user)->get(route('admin.event.show', $event));

    $response->assertOk();
    $response->assertSee('https://google.com');
    $response->assertSee('https://github.com');
});

test('public event detail page can handle links', function () {
    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
        'links' => ['https://google.com', 'https://github.com'],
    ]);

    $response = $this->get(route('event.show', $event));

    $response->assertOk();
    $response->assertSee('https://google.com');
    $response->assertSee('https://github.com');
});
