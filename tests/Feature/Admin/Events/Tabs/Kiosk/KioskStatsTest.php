<?php

use App\Livewire\Admin\Events\Tabs\Kiosk\Kiosk;
use App\Models\Event;
use App\Models\EventKiosk;
use App\Models\EventKioskArticle;
use App\Models\EventKioskCategory;
use App\Models\EventKioskPurchase;
use App\Models\EventKioskPurchaseItem;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

it('calculates kiosk statistics correctly', function () {
    $event = Event::factory()->create();
    $kiosk = new EventKiosk;
    $kiosk->event_id = $event->id;
    $kiosk->save();

    $category1 = EventKioskCategory::factory()->create(['kiosk_id' => $kiosk->id, 'name' => 'Drinks']);
    $category2 = EventKioskCategory::factory()->create(['kiosk_id' => $kiosk->id, 'name' => 'Snacks']);

    $article1 = EventKioskArticle::factory()->create([
        'kiosk_id' => $kiosk->id,
        'category_id' => $category1->id,
        'name' => 'Cola',
        'cost' => 15,
        'amount' => 100,
    ]);

    $article2 = EventKioskArticle::factory()->create([
        'kiosk_id' => $kiosk->id,
        'category_id' => $category2->id,
        'name' => 'Chips',
        'cost' => 20,
        'amount' => 50,
    ]);

    $user = User::factory()->create();

    // Create some purchases
    $purchase1 = new EventKioskPurchase;
    $purchase1->forceFill([
        'kiosk_id' => $kiosk->id,
        'operator_id' => $user->id,
        'cost' => 35,
        'created_at' => Carbon::today()->setHour(10)->setMinute(0)->setSecond(0),
    ])->save();

    EventKioskPurchaseItem::create([
        'purchase_id' => $purchase1->id,
        'article_id' => $article1->id,
        'amount' => 1,
        'cost' => 15,
    ]);

    EventKioskPurchaseItem::create([
        'purchase_id' => $purchase1->id,
        'article_id' => $article2->id,
        'amount' => 1,
        'cost' => 20,
    ]);

    $purchase2 = new EventKioskPurchase;
    $purchase2->forceFill([
        'kiosk_id' => $kiosk->id,
        'operator_id' => $user->id,
        'cost' => 15,
        'created_at' => Carbon::today()->setHour(11)->setMinute(0)->setSecond(0),
    ])->save();

    EventKioskPurchaseItem::create([
        'purchase_id' => $purchase2->id,
        'article_id' => $article1->id,
        'amount' => 1,
        'cost' => 15,
    ]);

    $admin = User::factory()->create();
    $admin->givePermissionTo('manage kiosk');

    $component = Livewire::actingAs($admin)->test(Kiosk::class, ['event' => $event]);

    $stats = $component->get('stats');

    expect($stats['total_revenue'])->toBe(50);

    // Category distribution
    expect($stats['category_distribution']['labels'])->toContain('Drinks', 'Snacks');
    // Drinks: 15 (p1) + 15 (p2) = 30
    // Snacks: 20 (p1) = 20
    expect($stats['category_distribution']['data'])->toContain(30, 20);

    // Top articles
    expect($stats['top_articles']['labels'])->toContain('Cola', 'Chips');
    // Cola: 2 sold
    // Chips: 1 sold
    expect($stats['top_articles']['quantities'])->toContain(2, 1);

    // Hourly sales
    expect($stats['hourly_sales']['labels'])->toContain('10:00', '11:00');
    expect($stats['hourly_sales']['data'])->toContain(35, 15);
});
