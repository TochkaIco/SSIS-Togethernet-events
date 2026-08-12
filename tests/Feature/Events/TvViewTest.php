<?php

declare(strict_types=1);

use App\EventType;
use App\Livewire\Events\TvView;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('countdown page renders successfully when event has not started', function () {
    $event = Event::factory()->create([
        'event_starts_at' => now()->addDays(2),
    ]);

    Livewire::test(TvView::class, ['event' => $event])
        ->assertSee($event->title)
        ->assertSee(__('Days Until Event'));
});

test('qr tag view renders when event starts', function () {
    $event = Event::factory()->create([
        'event_starts_at' => now()->subMinutes(10),
        'event_type' => EventType::QR_TAG,
    ]);

    Livewire::test(TvView::class, ['event' => $event])
        ->assertSee(__('Top Hunters'))
        ->assertSee(__('Rules'))
        ->assertSee(__('Game Active'));
});
