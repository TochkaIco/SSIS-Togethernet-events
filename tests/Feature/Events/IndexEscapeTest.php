<?php

use App\EventType;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\App;

test('event description in index view does not double escape quotes', function () {
    // Set locale to sv to match the reported issue context
    App::setLocale('sv');

    $event = Event::factory()->create([
        'title' => 'Test Event',
        'description' => 'Description with "quotes"',
        'event_type' => EventType::QR_TAG,
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
        'display_starts_at' => now()->subDay(),
    ]);

    $this->get(route('events'))
        ->assertStatus(200)
        ->assertSee('Description with "quotes"');
});

test('event description in admin index view does not double escape quotes', function () {
    App::setLocale('sv');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $event = Event::factory()->create([
        'title' => 'Admin Test Event',
        'description' => 'Admin description with "quotes"',
        'event_type' => EventType::QR_TAG,
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
        'display_starts_at' => now()->subDay(),
    ]);

    // Assuming the admin route is 'admin.events' or similar
    // Let's check the routes first.
    $this->get('/admin/events')
        ->assertStatus(200)
        ->assertSee('Admin description with "quotes"');
});
