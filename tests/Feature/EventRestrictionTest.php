<?php

use App\EventType;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cannot change critical fields if participants exist', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('create articles');
    $admin->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'event_type' => EventType::KARAOKE,
        'event_starts_at' => now()->addDay(),
        'one_hour_periods' => true,
        'one_hour_periods_number' => 2,
    ]);

    // Register a user
    $user = User::factory()->create();
    $event->registrations()->create([
        'user_id' => $user->id,
    ]);

    // Attempt to change start time
    $response = $this->actingAs($admin)->patch(route('admin.event.update', $event), [
        'title' => $event->title,
        'description' => $event->description,
        'event_type' => EventType::KARAOKE->value,
        'num_of_seats' => $event->num_of_seats,
        'one_hour_periods' => true,
        'one_hour_periods_number' => 2,
        'interval_length' => 0,
        'display_starts_at' => $event->display_starts_at->format('Y-m-d H:i:s'),
        'event_starts_at' => now()->addDays(2)->format('Y-m-d H:i:s'), // Changing start time
    ]);

    $response->assertSessionHasErrors(['event_starts_at']);
});

test('can change non-critical fields even if participants exist', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('create articles');
    $admin->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'event_type' => EventType::KARAOKE,
        'description' => 'Original description',
    ]);

    // Register a user
    $user = User::factory()->create();
    $event->registrations()->create([
        'user_id' => $user->id,
    ]);

    // Change description
    $response = $this->actingAs($admin)->patch(route('admin.event.update', $event), [
        'title' => $event->title,
        'description' => 'New description',
        'event_type' => EventType::KARAOKE->value,
        'num_of_seats' => $event->num_of_seats,
        'display_starts_at' => $event->display_starts_at->format('Y-m-d H:i:s'),
        'event_starts_at' => $event->event_starts_at->format('Y-m-d H:i:s'),
        'event_ends_at' => $event->event_ends_at->format('Y-m-d H:i:s'),
    ]);

    $response->assertSessionHasNoErrors();
    expect($event->refresh()->description)->toBe('New description');
});

test('cannot delete event older than 30 mins with participants', function () {
    $admin = User::factory()->create(['tos_accepted_at' => now()]);
    $admin->givePermissionTo('create articles');
    $admin->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'created_at' => now()->subMinutes(31),
    ]);

    // Register a user
    $user = User::factory()->create();
    $event->registrations()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.event.destroy', $event));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

test('can delete event older than 30 mins without participants', function () {
    $admin = User::factory()->create(['tos_accepted_at' => now()]);
    $admin->givePermissionTo('create articles');
    $admin->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'created_at' => now()->subMinutes(31),
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.event.destroy', $event));

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});

test('can delete event younger than 30 mins even with participants', function () {
    $admin = User::factory()->create(['tos_accepted_at' => now()]);
    $admin->givePermissionTo('create articles');
    $admin->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'created_at' => now()->subMinutes(10),
    ]);

    // Register a user
    $user = User::factory()->create();
    $event->registrations()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.event.destroy', $event));

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});
