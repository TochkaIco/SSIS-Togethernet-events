<?php

use App\Jobs\SendDiscordQrtagNotification;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\QrTagLog;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['services.discord.webhook_url' => 'https://discord.com/webhook']);
    Http::fake();
    Queue::fake();
});

it('dispatches a discord notification job when a qrtag log is created', function () {
    $event = Event::factory()->create(['title' => 'Test Event']);
    $user = User::factory()->create(['name' => 'Test User A']);
    $target = User::factory()->create(['name' => 'Test User B']);

    QrTagLog::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'target_user_id' => $target->id,
        'type' => 'tagged',
    ]);

    Queue::assertPushed(SendDiscordQrtagNotification::class, function (SendDiscordQrtagNotification $job): bool {
        return str_contains($job->message, 'Test User A kullade Test User B');
    });
});

it(/**
 * @throws ConnectionException
 */ 'sends the correct message to discord via the job', function () {
    $event = Event::factory()->create(['title' => 'Test Event']);
    $user = User::factory()->create(['name' => 'Test User A']);
    $target = User::factory()->create(['name' => 'Test User B']);

    // Create registrations to test player count
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'qr_tag_tagged_at' => null,
    ]);
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => $target->id,
        'qr_tag_tagged_at' => now(), // Already tagged
    ]);
    EventUser::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'qr_tag_tagged_at' => null,
    ]);

    $message = "Test User A kullade Test User B!\nNu är det 2 spelare kvar.";
    $job = new SendDiscordQrtagNotification($message);
    $job->handle();

    Http::assertSent(function (Request $request) use ($message): bool {
        return $request->url() === 'https://discord.com/webhook' &&
               $request['content'] === $message;
    });
});
