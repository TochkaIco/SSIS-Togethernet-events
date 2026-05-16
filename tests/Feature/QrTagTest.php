<?php

use App\Actions\RegisterUserToEvent;
use App\Actions\ShuffleQrTagTargets;
use App\Actions\UnregisterUserFromEvent;
use App\EventType;
use App\Livewire\Admin\Events\Tabs\QrTag;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('it shuffles targets in a single cycle', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        EventUser::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'in_waitinglist' => false,
        ]);
    }

    $action = new ShuffleQrTagTargets;
    $action->handle($event);

    $registrations = EventUser::where('event_id', $event->id)->get();

    foreach ($registrations as $reg) {
        expect($reg->qr_tag_token)->not->toBeNull();
        expect($reg->qr_tag_target_user_id)->not->toBeNull();
        expect($reg->qr_tag_target_user_id)->not->toBe($reg->user_id);
    }

    // Verify cycle: A -> B -> C -> A
    $first = $registrations[0];
    $current = $first;
    $visited = collect([$current->user_id]);

    for ($i = 0; $i < 2; $i++) {
        $next = $registrations->where('user_id', $current->qr_tag_target_user_id)->first();
        expect($visited->contains($next->user_id))->toBeFalse();
        $visited->push($next->user_id);
        $current = $next;
    }

    expect($current->qr_tag_target_user_id)->toBe($first->user_id);

    $this->assertDatabaseHas('qr_tag_logs', [
        'event_id' => $event->id,
        'type' => 'started',
    ]);
});

test('assassin can tag victim and inherit target', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $u3 = User::factory()->create();

    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_token' => 't1', 'qr_tag_target_user_id' => $u2->id]);
    $r2 = EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'qr_tag_token' => 't2', 'qr_tag_target_user_id' => $u3->id]);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_token' => 't3', 'qr_tag_target_user_id' => $u1->id]);

    Auth::login($u1);

    $response = $this->get(route('qr_tag.scan', ['token' => 't2']));

    $response->assertRedirect(route('event.show', $event));

    $r1->refresh();
    $r2->refresh();

    expect($r2->qr_tag_tagged_at)->not->toBeNull();
    expect($r2->qr_tag_tagged_by_user_id)->toBe($u1->id);
    expect($r1->qr_tag_target_user_id)->toBe($u3->id);
    expect($r1->has_arrived)->toBeTrue();

    $this->assertDatabaseHas('qr_tag_logs', [
        'event_id' => $event->id,
        'user_id' => $u1->id,
        'target_user_id' => $u2->id,
        'type' => 'tagged',
    ]);
});

test('cannot tag if not your target', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $u3 = User::factory()->create();

    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_token' => 't1', 'qr_tag_target_user_id' => $u2->id]);
    $r2 = EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'qr_tag_token' => 't2', 'qr_tag_target_user_id' => $u3->id]);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_token' => 't3', 'qr_tag_target_user_id' => $u1->id]);

    Auth::login($u1);

    // U1 targets U2. Trying to tag U3 (who targets U1)
    $response = $this->get(route('qr_tag.scan', ['token' => 't3']));

    $response->assertSessionHas('error', 'This is not your target.');

    $r3->refresh();
    expect($r3->qr_tag_tagged_at)->toBeNull();
});

test('cannot register to qr-tag event after it started', function () {
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_starts_at' => now()->subHour(),
        'display_starts_at' => now()->subDay(),
        'event_ends_at' => now()->addDay(),
    ]);

    $user = User::factory()->create();

    $action = app(RegisterUserToEvent::class);
    $result = $action->handle($user, $event);

    expect($result)->toBeNull();
    $this->assertDatabaseMissing('event_users', [
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);
});

test('cannot unregister from qr-tag event after it started', function () {
    $event = Event::factory()->create([
        'event_type' => EventType::QR_TAG,
        'event_starts_at' => now()->subHour(),
        'display_starts_at' => now()->subDay(),
        'event_ends_at' => now()->addDay(),
    ]);

    $user = User::factory()->create();
    $registration = EventUser::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    $action = app(UnregisterUserFromEvent::class);
    $action->handle($user, $event);

    $this->assertDatabaseHas('event_users', [
        'id' => $registration->id,
    ]);
});

test('admin can rebirth a player', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $u3 = User::factory()->create();

    // Cycle: U1 -> U3 -> U1 (U2 is out)
    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_target_user_id' => $u3->id]);
    $r2 = EventUser::create([
        'user_id' => $u2->id,
        'event_id' => $event->id,
        'qr_tag_tagged_at' => now(),
        'qr_tag_tagged_by_user_id' => $u1->id,
        'qr_tag_target_user_id' => null,
    ]);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_target_user_id' => $u1->id]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->call('rebirthPlayer', $r2->id);

    $r1->refresh();
    $r2->refresh();
    $r3->refresh();

    expect($r2->qr_tag_tagged_at)->toBeNull();

    // Check if inserted correctly.
    // If r1 was chosen as host: r1 -> r2 -> r3 -> r1
    // If r3 was chosen as host: r1 -> r3 -> r2 -> r1
    if ($r1->qr_tag_target_user_id === $u2->id) {
        expect($r2->qr_tag_target_user_id)->toBe($u3->id);
        expect($r3->qr_tag_target_user_id)->toBe($u1->id);
    } else {
        expect($r3->qr_tag_target_user_id)->toBe($u2->id);
        expect($r2->qr_tag_target_user_id)->toBe($u1->id);
        expect($r1->qr_tag_target_user_id)->toBe($u3->id);
    }

    $this->assertDatabaseHas('qr_tag_logs', [
        'event_id' => $event->id,
        'user_id' => $u2->id,
        'admin_id' => $admin->id,
        'type' => 'rebirth',
    ]);
});

test('admin can rebirth all players', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        EventUser::create(['user_id' => $user->id, 'event_id' => $event->id, 'qr_tag_tagged_at' => now()]);
    }

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->call('rebirthAll', app(ShuffleQrTagTargets::class));

    expect(EventUser::where('event_id', $event->id)->whereNull('qr_tag_tagged_at')->count())->toBe(3);

    $this->assertDatabaseHas('qr_tag_logs', [
        'event_id' => $event->id,
        'admin_id' => $admin->id,
        'type' => 'rebirth_all',
    ]);
});

test('admin cannot shuffle if game already started', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $users = User::factory()->count(3)->create();
    foreach ($users as $index => $user) {
        EventUser::create(['user_id' => $user->id, 'event_id' => $event->id, 'qr_tag_token' => 'token'.$index]);
    }

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->call('startShuffle', app(ShuffleQrTagTargets::class));

    // Verify it didn't create another 'started' log (one exists from manual setup if we wanted, but here we didn't call the action yet)
    // Actually, in our manual setup we didn't create a log.
    $this->assertDatabaseMissing('qr_tag_logs', [
        'event_id' => $event->id,
        'type' => 'started',
    ]);
});

test('shuffle button is hidden if game started', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $users = User::factory()->count(3)->create();
    foreach ($users as $index => $user) {
        EventUser::create(['user_id' => $user->id, 'event_id' => $event->id, 'qr_tag_token' => 'token'.$index]);
    }

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->assertDontSee(__('Shuffle & Start'))
        ->assertSee(__('Reset Game'))
        ->assertSee(__('Rebirth All'));
});

test('shuffle button is visible if game not started', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->assertSee(__('Shuffle & Start'))
        ->assertDontSee(__('Reset Game'))
        ->assertDontSee(__('Rebirth All'));
});
