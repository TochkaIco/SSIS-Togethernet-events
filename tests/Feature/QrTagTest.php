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
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Http::fake();
});

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

test('admin can respawn a player', function () {
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
        ->call('respawnPlayer', $r2->id);

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
        'type' => 'respawn',
    ]);
});

test('admin can respawn all players', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        EventUser::create(['user_id' => $user->id, 'event_id' => $event->id, 'qr_tag_tagged_at' => now()]);
    }

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->call('respawnAll', app(ShuffleQrTagTargets::class));

    expect(EventUser::where('event_id', $event->id)->whereNull('qr_tag_tagged_at')->count())->toBe(3);

    $this->assertDatabaseHas('qr_tag_logs', [
        'event_id' => $event->id,
        'admin_id' => $admin->id,
        'type' => 'respawn_all',
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
        ->assertSee(__('Respawn All'));
});

test('shuffle button is visible if game not started', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->assertSee(__('Shuffle & Start'))
        ->assertDontSee(__('Reset Game'))
        ->assertDontSee(__('Respawn All'));
});

test('it tracks tag counts and shows leaderboard', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create(['name' => 'Assassin']);
    $u2 = User::factory()->create(['name' => 'Victim 1']);
    $u3 = User::factory()->create(['name' => 'Victim 2']);
    $u4 = User::factory()->create(['name' => 'Target 3']);

    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_token' => 't1', 'qr_tag_target_user_id' => $u2->id, 'qr_tag_count' => 0, 'in_waitinglist' => false]);
    $r2 = EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'qr_tag_token' => 't2', 'qr_tag_target_user_id' => $u3->id, 'qr_tag_count' => 0, 'in_waitinglist' => false]);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_token' => 't3', 'qr_tag_target_user_id' => $u4->id, 'qr_tag_count' => 0, 'in_waitinglist' => false]);
    $r4 = EventUser::create(['user_id' => $u4->id, 'event_id' => $event->id, 'qr_tag_token' => 't4', 'qr_tag_target_user_id' => $u1->id, 'qr_tag_count' => 0, 'in_waitinglist' => false]);

    Auth::login($u1);

    $event->refresh();

    // Tag 1
    $response = $this->get(route('qr_tag.scan', ['token' => 't2']));
    $response->assertRedirect(route('event.show', $event));
    $response->assertSessionHas('success', __('Target tagged! You have a new target.'));

    $r1->refresh();
    expect($r1->qr_tag_count)->toBe(1);
    expect($r1->qr_tag_target_user_id)->toBe($u3->id);

    // Check leaderboard
    $event->refresh();
    $leaderboard = $event->qrTagLeaderboard();
    expect($leaderboard->first()->user_id)->toBe($u1->id);
    expect($leaderboard->first()->qr_tag_count)->toBe(1);
    // Victim 1 (u2) was tagged, so it should NOT be in the leaderboard
    expect($leaderboard->pluck('user_id'))->not->toContain($u2->id);

    // Give Target 3 (u4) some tags manually
    $r4->update(['qr_tag_count' => 10]);
    expect(EventUser::find($r4->id)->qr_tag_count)->toBe(10);
    $event->refresh();
    $leaderboard = $event->qrTagLeaderboard();
    expect($leaderboard->pluck('qr_tag_count')->toArray())->toBe([10, 1, 0]);
    expect($leaderboard->first()->user_id)->toBe($u4->id);

    // Tag u3 (Victim 2) by u1
    $this->get(route('qr_tag.scan', ['token' => 't3']));
    $r1->refresh();
    expect($r1->qr_tag_count)->toBe(2);
    expect($r1->qr_tag_target_user_id)->toBe($u4->id);

    // Now tag u4 (who has 10 tags)
    $this->get(route('qr_tag.scan', ['token' => 't4']));
    $r4->refresh();
    expect($r4->qr_tag_tagged_at)->not->toBeNull();

    // Now u4 should be gone from leaderboard despite having 10 tags
    expect($event->qrTagLeaderboard()->pluck('user_id'))->not->toContain($u4->id);
    expect($event->qrTagLeaderboard()->first()->user_id)->toBe($u1->id);
    expect($event->qrTagLeaderboard()->first()->qr_tag_count)->toBe(3);

    // Verify token was regenerated for victims
    $r2->refresh();
    $r3->refresh();
    $r4->refresh();
    expect($r2->qr_tag_token)->not->toBe('t2');
    expect($r3->qr_tag_token)->not->toBe('t3');
    expect($r4->qr_tag_token)->not->toBe('t4');

    // Respawn u2
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(QrTag::class, ['event' => $event])
        ->call('respawnPlayer', $r2->id);

    $r1->refresh();
    // Count should NOT reset
    expect($r1->qr_tag_count)->toBe(3);
});

test('it can search and filter participants', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com', 'class' => 'TE24A']);
    $u2 = User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com', 'class' => 'TE25B']);

    $role = Role::create(['name' => 'test-role']);
    $u1->assignRole($role);

    EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'in_waitinglist' => false]);
    EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'in_waitinglist' => false]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    // Search by name
    Livewire::test(QrTag::class, ['event' => $event])
        ->set('search', 'Alice')
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');

    // Search by email
    Livewire::test(QrTag::class, ['event' => $event])
        ->set('search', 'bob@example.com')
        ->assertSee('Bob Jones')
        ->assertDontSee('Alice Smith');

    // Filter by role
    Livewire::test(QrTag::class, ['event' => $event])
        ->set('filterRole', 'test-role')
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');

    // Filter by class group
    Livewire::test(QrTag::class, ['event' => $event])
        ->set('filterClassGroup', 'TE24')
        ->assertSee('Alice Smith')
        ->assertDontSee('Bob Jones');
});

test('it respects disabled players', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create(['name' => 'Active']);
    $u2 = User::factory()->create(['name' => 'Disabled']);
    $u3 = User::factory()->create(['name' => 'Active 2']);

    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_token' => 't1', 'qr_tag_target_user_id' => $u2->id, 'in_waitinglist' => false]);
    $r2 = EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'qr_tag_token' => 't2', 'qr_tag_target_user_id' => $u3->id, 'in_waitinglist' => false, 'is_disabled' => true]);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_token' => 't3', 'qr_tag_target_user_id' => $u1->id, 'in_waitinglist' => false]);

    Auth::login($u1);

    // Cannot tag disabled victim
    $response = $this->get(route('qr_tag.scan', ['token' => 't2']));
    $response->assertRedirect(route('event.show', $event));
    $response->assertSessionHas('error', __('This user is currently disabled.'));

    // Assassin cannot tag if disabled
    $r1->update(['is_disabled' => true]);
    $r2->update(['is_disabled' => false]);
    $response = $this->get(route('qr_tag.scan', ['token' => 't2']));
    $response->assertSessionHas('error', __('You are currently disabled.'));

    // Shuffle excludes disabled players
    $r1->update(['is_disabled' => false]);
    $r2->update(['is_disabled' => true]);

    $action = new ShuffleQrTagTargets;
    $action->handle($event);

    $r2->refresh();
    // r2 should NOT have a target or token because it was excluded from shuffle
    // Wait, ShuffleQrTagTargets resets targets/tokens for those it processes.
    // Let's check the code.
    /*
    foreach ($participants as $index => $participant) {
        $participant->update([...]);
    }
    */
    // If r2 is not in $participants, it won't be updated.

    expect(EventUser::where('event_id', $event->id)->where('is_disabled', false)->whereNotNull('qr_tag_target_user_id')->count())->toBe(2);
    expect($r2->qr_tag_target_user_id)->toBe($u3->id); // Remained from manual setup
});

test('it repairs cycle when a player is disabled during game', function () {
    $event = Event::factory()->create(['event_type' => EventType::QR_TAG]);
    $u1 = User::factory()->create(['name' => 'A']);
    $u2 = User::factory()->create(['name' => 'B']);
    $u3 = User::factory()->create(['name' => 'C']);

    // Cycle: A -> B -> C -> A
    $r1 = EventUser::create(['user_id' => $u1->id, 'event_id' => $event->id, 'qr_tag_target_user_id' => $u2->id, 'in_waitinglist' => false, 'qr_tag_token' => 't1']);
    $r2 = EventUser::create(['user_id' => $u2->id, 'event_id' => $event->id, 'qr_tag_target_user_id' => $u3->id, 'in_waitinglist' => false, 'qr_tag_token' => 't2']);
    $r3 = EventUser::create(['user_id' => $u3->id, 'event_id' => $event->id, 'qr_tag_target_user_id' => $u1->id, 'in_waitinglist' => false, 'qr_tag_token' => 't3']);

    // Disable B
    $r2->disable();

    $r1->refresh();
    $r2->refresh();
    $r3->refresh();

    expect($r2->is_disabled)->toBeTrue();
    expect($r2->qr_tag_target_user_id)->toBeNull();
    // A should now target C (B's old target)
    expect($r1->qr_tag_target_user_id)->toBe($u3->id);
    // C still targets A
    expect($r3->qr_tag_target_user_id)->toBe($u1->id);

    // Re-enable B
    $r2->enable();

    $r1->refresh();
    $r2->refresh();
    $r3->refresh();

    expect($r2->is_disabled)->toBeFalse();
    expect($r2->qr_tag_target_user_id)->not->toBeNull();

    // Check if cycle is consistent: everyone has a unique target and is targeted
    $players = [$r1, $r2, $r3];
    $targets = collect($players)->pluck('qr_tag_target_user_id');
    expect($targets->unique()->count())->toBe(3);
    expect($targets->contains($u1->id))->toBeTrue();
    expect($targets->contains($u2->id))->toBeTrue();
    expect($targets->contains($u3->id))->toBeTrue();
});
