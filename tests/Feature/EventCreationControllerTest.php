<?php

use App\EventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('event can be created through controller', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create articles');

    $response = $this->actingAs($user)->post(route('admin.event.store'), [
        'title' => 'Test Title',
        'description' => 'Test Description',
        'event_type' => EventType::KARAOKE->value,
        'display_starts_at' => now()->format('Y-m-d H:i:s'),
        'event_starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'event_ends_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect(route('admin.events'));
    $this->assertDatabaseHas('events', [
        'title' => 'Test Title',
        'event_type' => EventType::KARAOKE->value,
    ]);
});
