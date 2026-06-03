<?php

use App\EventType;
use App\Livewire\Admin\Events\Index as AdminEventsIndex;
use App\Livewire\Events\Index as EventsIndex;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

test('public events can be filtered by type', function () {
    Event::factory()->create([
        'title' => 'Karaoke Event',
        'event_type' => EventType::KARAOKE,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    Event::factory()->create([
        'title' => 'Film Party Event',
        'event_type' => EventType::FILM_PARTY,
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    Livewire::test(EventsIndex::class)
        ->assertSee('Karaoke Event')
        ->assertSee('Film Party Event')
        ->set('filterType', EventType::KARAOKE->value)
        ->assertSee('Karaoke Event')
        ->assertDontSee('Film Party Event');
});

test('public events can be filtered by status', function () {
    Event::factory()->create([
        'title' => 'Upcoming Event',
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    Event::factory()->create([
        'title' => 'Active Event',
        'display_starts_at' => now()->subDay(),
        'event_starts_at' => now()->subHour(),
        'event_ends_at' => now()->addHour(),
    ]);

    Event::factory()->create([
        'title' => 'Finished Event',
        'display_starts_at' => now()->subDays(3),
        'event_starts_at' => now()->subDays(2),
        'event_ends_at' => now()->subDay(),
    ]);

    Livewire::test(EventsIndex::class)
        ->set('filterStatus', 'upcoming')
        ->assertSee('Upcoming Event')
        ->assertDontSee('Active Event')
        ->assertDontSee('Finished Event')
        ->set('filterStatus', 'active')
        ->assertDontSee('Upcoming Event')
        ->assertSee('Active Event')
        ->assertDontSee('Finished Event')
        ->set('filterStatus', 'finished')
        ->assertDontSee('Upcoming Event')
        ->assertDontSee('Active Event')
        ->assertSee('Finished Event');
});

test('admin events can be searched by title', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Event::factory()->create(['title' => 'Searchable Event']);
    Event::factory()->create(['title' => 'Other Event']);

    Livewire::test(AdminEventsIndex::class)
        ->assertSee('Searchable Event')
        ->assertSee('Other Event')
        ->set('search', 'Searchable')
        ->assertSee('Searchable Event')
        ->assertDontSee('Other Event');
});

test('admin events can be filtered by type and status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Event::factory()->create([
        'title' => 'Admin Karaoke Upcoming',
        'event_type' => EventType::KARAOKE,
        'event_starts_at' => now()->addDay(),
        'event_ends_at' => now()->addDays(2),
    ]);

    Event::factory()->create([
        'title' => 'Admin Film Active',
        'event_type' => EventType::FILM_PARTY,
        'event_starts_at' => now()->subHour(),
        'event_ends_at' => now()->addHour(),
    ]);

    Livewire::test(AdminEventsIndex::class)
        ->set('filterType', EventType::KARAOKE->value)
        ->assertSee('Admin Karaoke Upcoming')
        ->assertDontSee('Admin Film Active')
        ->set('filterType', '')
        ->set('filterStatus', 'active')
        ->assertDontSee('Admin Karaoke Upcoming')
        ->assertSee('Admin Film Active');
});
