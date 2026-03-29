<?php

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('public event detail page can be rendered', function () {
    $event = Event::factory()->create([
        'display_starts_at' => now()->subDay(),
    ]);

    $response = $this->get(route('event.show', $event));

    $response->assertOk();
    $response->assertSee($event->title);
});

test('admin event detail page can be rendered as admin', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $event = Event::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.event.show', $event));

    $response->assertOk();
    $response->assertSee($event->title);
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
