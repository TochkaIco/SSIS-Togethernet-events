<?php

declare(strict_types=1);

use App\Mail\NewTermsMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

test('it sends TOS update notifications to users who haven\'t accepted yet', function () {
    Mail::fake();

    $user = User::factory()->create([
        'tos_accepted_at' => null,
        'tos_warning_sent_at' => null,
    ]);

    artisan('app:notify-tos-update');

    $user->refresh();
    expect($user->tos_warning_sent_at)->not->toBeNull();
    Mail::assertQueued(NewTermsMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('it does not resend TOS notification if already sent', function () {
    Mail::fake();

    $user = User::factory()->create([
        'tos_accepted_at' => null,
        'tos_warning_sent_at' => now()->subDays(2),
    ]);

    artisan('app:notify-tos-update');

    Mail::assertNothingQueued();
});

test('it anonymizes users who did not accept TOS after one month', function () {
    Carbon::setTestNow('2026-06-01 12:00:00');

    $user = User::factory()->create([
        'tos_accepted_at' => null,
        'tos_warning_sent_at' => now()->subMonth()->subDay(),
    ]);

    artisan('app:notify-tos-update');

    expect($user->refresh()->isAnonymized())->toBeTrue();
});

test('it does not anonymize users who accepted TOS', function () {
    Carbon::setTestNow('2026-06-01 12:00:00');

    $user = User::factory()->create([
        'tos_accepted_at' => now(),
        'tos_warning_sent_at' => now()->subMonth()->subDay(),
    ]);

    artisan('app:notify-tos-update');

    expect($user->refresh()->isAnonymized())->toBeFalse();
});
