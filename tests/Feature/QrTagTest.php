<?php

use App\Actions\RegisterUserToEvent;
use App\Actions\ShuffleQrTagTargets;
use App\Actions\UnregisterUserFromEvent;
use App\EventType;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
