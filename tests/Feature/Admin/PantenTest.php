<?php

declare(strict_types=1);

use App\Livewire\Admin\AppConfigurationPage;
use App\Livewire\Admin\Panten\Index as PantenIndex;
use App\Models\AppConfig;
use App\Models\PantAlert;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('unauthorized users cannot view panten page', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');

    $this->actingAs($user)
        ->get(route('admin.panten.index'))
        ->assertForbidden();
});

test('authorized users can view panten page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $this->actingAs($user)
        ->get(route('admin.panten.index'))
        ->assertOk();
});

test('admins can configure pant swish number', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AppConfigurationPage::class)
        ->set('pantSwishNumber', '1234567890')
        ->assertHasNoErrors();

    expect(AppConfig::get('pant_swish_number'))->toBe('1234567890');
});

test('admin can activate a pant alert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    AppConfig::updateOrCreate(['key' => 'pant_swish_number'], ['value' => '1234567890', 'type' => 'string']);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('activateAlert')
        ->assertHasNoErrors();

    $alert = PantAlert::active()->first();
    expect($alert)->not->toBeNull();
    expect($alert->is_complete)->toBeFalse();
    expect($alert->receiver_swish)->toBe('1234567890');
});

test('non-admin cannot activate a pant alert', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->call('activateAlert')
        ->assertStatus(403);

    expect(PantAlert::active()->count())->toBe(0);
});

test('only one pant alert can be active at a time', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    PantAlert::factory()->create(['is_complete' => false]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('activateAlert');

    expect(PantAlert::count())->toBe(1);
});

test('member can complete pant alert using Swish method', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $member1 = User::factory()->create();
    $member2 = User::factory()->create();

    $alert = PantAlert::factory()->create(['is_complete' => false]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->set('completionMethod', 'swish')
        ->set('sekReceived', '150.50')
        ->set('completedBy', [$member1->id, $member2->id])
        ->call('completeAlert')
        ->assertHasNoErrors();

    $alert->refresh();
    expect($alert->is_complete)->toBeTrue();
    expect($alert->sek_received)->toBe(150.50);
    expect($alert->completed_by)->toBe([$member1->id, $member2->id, $user->id]);
    expect($alert->receipt_path)->toBeNull();
});

test('member can complete pant alert using Check upload method', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $member1 = User::factory()->create();

    $alert = PantAlert::factory()->create(['is_complete' => false]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->set('completionMethod', 'check')
        ->set('sekReceived', '200')
        ->set('completedBy', [$member1->id])
        ->set('receiptPhoto', UploadedFile::fake()->image('check.jpg'))
        ->call('completeAlert')
        ->assertHasNoErrors();

    $alert->refresh();
    expect($alert->is_complete)->toBeTrue();
    expect($alert->sek_received)->toBe(200.0);
    expect($alert->completed_by)->toBe([$member1->id, $user->id]);
    expect($alert->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($alert->receipt_path);
});

test('admin can confirm receipt of a check pant alert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => 'pant_receipts/check.jpg',
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('confirmReceipt', $alert->id)
        ->assertHasNoErrors()
        ->assertSet('confirmingReceiptId', $alert->id)
        ->call('executeConfirmReceipt')
        ->assertHasNoErrors()
        ->assertSet('confirmingReceiptId', null);

    $alert->refresh();
    expect($alert->admin_user_id)->toBe($admin->id);
});

test('admin can confirm receipt of a Swish pant alert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => null,
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('confirmReceipt', $alert->id)
        ->assertHasNoErrors()
        ->assertSet('confirmingReceiptId', $alert->id)
        ->call('executeConfirmReceipt')
        ->assertHasNoErrors()
        ->assertSet('confirmingReceiptId', null);

    $alert->refresh();
    expect($alert->admin_user_id)->toBe($admin->id);
});

test('non-admin cannot confirm receipt of a check pant alert', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => 'pant_receipts/check.jpg',
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->call('confirmReceipt', $alert->id)
        ->assertStatus(403);

    $alert->refresh();
    expect($alert->admin_user_id)->toBeNull();
});

test('non-admin cannot execute confirm receipt of a check pant alert', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => 'pant_receipts/check.jpg',
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->set('confirmingReceiptId', $alert->id)
        ->call('executeConfirmReceipt')
        ->assertStatus(403);

    $alert->refresh();
    expect($alert->admin_user_id)->toBeNull();
});

test('stats and leaderboard compute correctly', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $member1 = User::factory()->create(['name' => 'Recycler One']);
    $member2 = User::factory()->create(['name' => 'Recycler Two']);

    // completed last year
    PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [$member1->id],
        'sek_received' => 100,
        'created_at' => now()->subYear(),
    ]);

    // completed this year
    PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [$member1->id, $member2->id],
        'sek_received' => 150,
        'created_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->assertViewHas('totalSek', 250.0)
        ->assertViewHas('yearlySek', 150.0)
        ->assertViewHas('yearlyCount', 1)
        ->assertViewHas('leaderboard', function ($leaderboard) use ($member1, $member2): bool {
            return count($leaderboard) === 2 &&
                $leaderboard[0]->user->id === $member1->id &&
                $leaderboard[0]->count === 2 &&
                $leaderboard[1]->user->id === $member2->id &&
                $leaderboard[1]->count === 1;
        });
});
