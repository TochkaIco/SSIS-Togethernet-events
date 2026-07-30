<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventKiosk;
use App\Models\EventKioskArticle;
use App\Models\EventKioskCategory;

test('event kiosk category can be created and belongs to a kiosk', function () {
    $event = Event::factory()->create();

    $kiosk = new EventKiosk;
    $kiosk->event_id = $event->id;
    $kiosk->swish_number = '1234567890';
    $kiosk->save();

    $category = EventKioskCategory::factory()->create([
        'kiosk_id' => $kiosk->id,
    ]);

    expect($category->kiosk)->toBeInstanceOf(EventKiosk::class)
        ->and($category->kiosk->id)->toBe($kiosk->id)
        ->and($category->name)->not->toBeEmpty();
});

test('event kiosk category has many articles', function () {
    $event = Event::factory()->create();

    $kiosk = new EventKiosk;
    $kiosk->event_id = $event->id;
    $kiosk->swish_number = '1234567890';
    $kiosk->save();

    $category = EventKioskCategory::factory()->create([
        'kiosk_id' => $kiosk->id,
    ]);

    $article1 = EventKioskArticle::factory()->create([
        'category_id' => $category->id,
        'kiosk_id' => $kiosk->id,
    ]);

    $article2 = EventKioskArticle::factory()->create([
        'category_id' => $category->id,
        'kiosk_id' => $kiosk->id,
    ]);

    expect($category->articles)->toHaveCount(2)
        ->and($category->articles->pluck('id'))->toContain($article1->id, $article2->id);
});
