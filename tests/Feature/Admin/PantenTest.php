<?php

declare(strict_types=1);

use App\Jobs\SendDiscordPantCompletionAlert;
use App\Livewire\Admin\AppConfigurationPage;
use App\Livewire\Admin\Panten\Index as PantenIndex;
use App\Models\AppConfig;
use App\Models\PantAlert;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('unauthorized users cannot view panten page', function () {
    $user = User::factory()->create();

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

test('admins can configure discord togethernet role id', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(AppConfigurationPage::class)
        ->set('discordTogethernetRoleId', '123456789012345678')
        ->assertHasNoErrors();

    expect(AppConfig::get('discord_togethernet_role_id'))->toBe('123456789012345678');

    Livewire::actingAs($admin)
        ->test(AppConfigurationPage::class)
        ->set('discordTogethernetRoleId', 'not-a-number')
        ->assertHasErrors(['discordTogethernetRoleId']);

    // Database should still have the old valid value because validation failed
    expect(AppConfig::get('discord_togethernet_role_id'))->toBe('123456789012345678');
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

test('cannot activate new pant alert if there is a completed but unconfirmed pant alert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    PantAlert::factory()->create([
        'is_complete' => true,
        'admin_user_id' => null,
    ]);

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

test('admin can stop an active pant alert', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $alert = PantAlert::factory()->create(['is_complete' => false]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('stopAlert')
        ->assertHasNoErrors();

    expect(PantAlert::find($alert->id))->toBeNull();
});

test('non-admin cannot stop a pant alert', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $alert = PantAlert::factory()->create(['is_complete' => false]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->call('stopAlert')
        ->assertStatus(403);

    expect(PantAlert::find($alert->id))->not->toBeNull();
});

test('admin can decline a completion submission', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $filePath = 'pant_receipts/receipt.jpg';
    Storage::disk('public')->put($filePath, 'fake image content');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [1, 2],
        'sek_received' => 120.0,
        'receipt_path' => $filePath,
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('declineCompletion', $alert->id)
        ->assertHasNoErrors()
        ->assertSet('decliningAlertId', $alert->id)
        ->call('executeDeclineCompletion')
        ->assertHasNoErrors()
        ->assertSet('decliningAlertId', null);

    $alert->refresh();
    expect($alert->is_complete)->toBeFalse();
    expect($alert->completed_by)->toBeNull();
    expect($alert->sek_received)->toBe(0.0);
    expect($alert->receipt_path)->toBeNull();
    Storage::disk('public')->assertMissing($filePath);
});

test('non-admin cannot decline a completion submission', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $filePath = 'pant_receipts/receipt.jpg';
    Storage::disk('public')->put($filePath, 'fake image content');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [1, 2],
        'sek_received' => 120.0,
        'receipt_path' => $filePath,
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->call('declineCompletion', $alert->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->set('decliningAlertId', $alert->id)
        ->call('executeDeclineCompletion')
        ->assertStatus(403);

    $alert->refresh();
    expect($alert->is_complete)->toBeTrue();
    Storage::disk('public')->assertExists($filePath);
});

test('admin can delete a past alert from history', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $filePath = 'pant_receipts/receipt.jpg';
    Storage::disk('public')->put($filePath, 'fake image content');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => $filePath,
    ]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('deleteAlert', $alert->id)
        ->assertHasNoErrors()
        ->assertSet('deletingAlertId', $alert->id)
        ->call('executeDeleteAlert')
        ->assertHasNoErrors()
        ->assertSet('deletingAlertId', null);

    expect(PantAlert::find($alert->id))->toBeNull();
    Storage::disk('public')->assertMissing($filePath);
});

test('non-admin cannot delete a past alert from history', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $user->givePermissionTo('manage panten');

    $filePath = 'pant_receipts/receipt.jpg';
    Storage::disk('public')->put($filePath, 'fake image content');

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'receipt_path' => $filePath,
    ]);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->call('deleteAlert', $alert->id)
        ->assertStatus(403);

    Livewire::actingAs($user)
        ->test(PantenIndex::class)
        ->set('deletingAlertId', $alert->id)
        ->call('executeDeleteAlert')
        ->assertStatus(403);

    expect(PantAlert::find($alert->id))->not->toBeNull();
    Storage::disk('public')->assertExists($filePath);
});

test('confirming receipt dispatches SendDiscordPantCompletionAlert with correct message format', function () {
    Queue::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $member1 = User::factory()->create(['name' => 'Alice']);
    $member2 = User::factory()->create(['name' => 'Bob']);

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [$member1->id, $member2->id],
        'sek_received' => 150.50,
        'admin_user_id' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(PantenIndex::class)
        ->call('confirmReceipt', $alert->id)
        ->call('executeConfirmReceipt');

    Queue::assertPushed(SendDiscordPantCompletionAlert::class, function ($job) use ($alert) {
        return $job->alert->id === $alert->id;
    });
});

test('SendDiscordPantCompletionAlert sends post request to discord togethernet webhook url', function () {
    Http::fake();

    config(['services.discord.togethernet_webhook_url' => 'https://discord.com/api/webhooks/test']);

    $member1 = User::factory()->create(['name' => 'Alice']);
    $member2 = User::factory()->create(['name' => 'Bob']);

    $alert = PantAlert::factory()->create([
        'is_complete' => true,
        'completed_by' => [$member1->id, $member2->id],
        'sek_received' => 150.50,
    ]);

    $job = new SendDiscordPantCompletionAlert($alert);
    $job->handle();

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://discord.com/api/webhooks/test' &&
            str_contains($request['content'], 'Alice, Bob') &&
            str_contains($request['content'], '150,50 kr');
    });
});
