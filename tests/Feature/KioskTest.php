<?php

use App\Livewire\Admin\Events\Tabs\Kiosk\Kiosk;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

it('does not crash when updating an article with no existing image path', function () {
    Storage::fake('public');

    // Setup permission
    Permission::firstOrCreate(['name' => 'manage kiosk', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('manage kiosk');

    $event = Event::factory()->create();
    $kiosk = $event->kiosk()->create();
    $category = $kiosk->categories()->create(['name' => 'Test Category']);
    $article = $kiosk->articles()->create([
        'name' => 'Test Article',
        'category_id' => $category->id,
        'cost' => 10,
        'amount' => 100,
        'image_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test(Kiosk::class, ['event' => $event])
        ->call('openArticleModal', $article->id)
        ->set('image', UploadedFile::fake()->image('large-image.jpg')->size(10000)) // 10MB
        ->call('saveArticle')
        ->assertHasNoErrors();

    $article->refresh();
    expect($article->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($article->image_path);
});

it('correctly imports kiosk from another event including images', function () {
    Storage::fake('public');

    // Setup permission
    Permission::firstOrCreate(['name' => 'manage kiosk', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('manage kiosk');

    // Source event
    $sourceEvent = Event::factory()->create(['title' => 'Source Event']);
    $sourceKiosk = $sourceEvent->kiosk()->create();
    $sourceCategory = $sourceKiosk->categories()->create(['name' => 'Source Category']);

    // Create source image
    $sourceImagePath = 'kiosk/source-image.jpg';
    Storage::disk('public')->put($sourceImagePath, 'content');

    $sourceArticle = $sourceKiosk->articles()->create([
        'name' => 'Source Article',
        'category_id' => $sourceCategory->id,
        'cost' => 15,
        'amount' => 50,
        'image_path' => $sourceImagePath,
    ]);

    // Destination event
    $destEvent = Event::factory()->create(['title' => 'Dest Event']);
    $destEvent->kiosk()->create();

    Livewire::actingAs($user)
        ->test(Kiosk::class, ['event' => $destEvent])
        ->set('importEventId', $sourceEvent->id)
        ->call('importFromEvent')
        ->assertHasNoErrors();

    $destEvent->refresh();
    $destKiosk = $destEvent->kiosk;
    expect($destKiosk->articles)->toHaveCount(1);

    $destArticle = $destKiosk->articles->first();
    expect($destArticle->name)->toBe('Source Article');
    expect($destArticle->cost)->toBe(15);
    expect($destArticle->image_path)->not->toBeNull();
    expect($destArticle->image_path)->not->toBe($sourceImagePath); // Should be a new UUID
    expect($destArticle->image_path)->toStartWith('kiosk/');

    Storage::disk('public')->assertExists($destArticle->image_path);
    expect($destKiosk->categories)->toHaveCount(1);
    expect($destArticle->category_id)->not->toBe($sourceCategory->id);
    expect($destArticle->category->name)->toBe('Source Category');
});

it('correctly deletes an article and its image', function () {
    Storage::fake('public');

    // Setup permission
    Permission::firstOrCreate(['name' => 'manage kiosk', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('manage kiosk');

    $event = Event::factory()->create();
    $kiosk = $event->kiosk()->create();
    $category = $kiosk->categories()->create(['name' => 'Test Category']);

    $imagePath = 'kiosk/to-delete.jpg';
    Storage::disk('public')->put($imagePath, 'content');

    $article = $kiosk->articles()->create([
        'name' => 'Delete Me',
        'category_id' => $category->id,
        'cost' => 10,
        'amount' => 100,
        'image_path' => $imagePath,
    ]);

    Livewire::actingAs($user)
        ->test(Kiosk::class, ['event' => $event])
        ->call('confirmDeleteArticle', $article->id)
        ->call('deleteArticle');

    expect($kiosk->articles()->count())->toBe(0);
    Storage::disk('public')->assertMissing($imagePath);
});

it('can remove an image from an existing article', function () {
    Storage::fake('public');

    Permission::firstOrCreate(['name' => 'manage kiosk', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->givePermissionTo('manage kiosk');

    $event = Event::factory()->create();
    $kiosk = $event->kiosk()->create();
    $category = $kiosk->categories()->create(['name' => 'Test Category']);

    $imagePath = 'kiosk/to-remove.jpg';
    Storage::disk('public')->put($imagePath, 'content');

    $article = $kiosk->articles()->create([
        'name' => 'Remove My Image',
        'category_id' => $category->id,
        'cost' => 10,
        'amount' => 100,
        'image_path' => $imagePath,
    ]);

    Livewire::actingAs($user)
        ->test(Kiosk::class, ['event' => $event])
        ->call('openArticleModal', $article->id)
        ->call('removeImage')
        ->call('saveArticle');

    $article->refresh();
    expect($article->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($imagePath);
});
