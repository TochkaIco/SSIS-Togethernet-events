<?php

use App\EventType;
use App\Models\Event;
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
        'num_of_seats' => '20',
        'paid_entry' => '0',
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

test('qr tag event can be created without seats, title and description', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create articles');

    $eventDate = now()->addDay();

    $response = $this->actingAs($user)->post(route('admin.event.store'), [
        'event_type' => EventType::QR_TAG->value,
        'paid_entry' => '0',
        'display_starts_at' => now()->format('Y-m-d H:i:s'),
        'event_starts_at' => $eventDate->format('Y-m-d H:i:s'),
        'event_ends_at' => $eventDate->addHours(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertRedirect(route('admin.events'));

    $event = Event::where('event_type', EventType::QR_TAG)->first();
    expect($event->title)->toBe('QR-Tag '.$eventDate->format('Y-m-d'));
    expect($event->description)->not->toBeEmpty();
    expect($event->num_of_seats)->toBe(1000000);
});

test('qr tag event title updates when start date changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create articles');
    $user->givePermissionTo('edit articles');

    $oldDate = now()->addDay();
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'title' => 'QR-Tag '.$oldDate->format('Y-m-d'),
        'event_starts_at' => $oldDate,
    ]);

    $newDate = now()->addDays(5);

    $response = $this->actingAs($user)->patch(route('admin.event.update', $event), [
        'event_type' => EventType::QR_TAG->value,
        'display_starts_at' => now()->format('Y-m-d H:i:s'),
        'event_starts_at' => $newDate->format('Y-m-d H:i:s'),
        'event_ends_at' => $newDate->addHours(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'QR-Tag '.$newDate->format('Y-m-d'),
    ]);
});

test('qr tag event can be updated with null seats', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create articles');
    $user->givePermissionTo('edit articles');

    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'num_of_seats' => 50,
    ]);

    $response = $this->actingAs($user)->patch(route('admin.event.update', $event), [
        'title' => 'Updated QR Tag Event',
        'event_type' => EventType::QR_TAG->value,
        'num_of_seats' => null,
        'paid_entry' => '0',
        'display_starts_at' => now()->format('Y-m-d H:i:s'),
        'event_starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'event_ends_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'Updated QR Tag Event',
        'num_of_seats' => 1000000,
    ]);
});
