<?php

declare(strict_types=1);

use App\Mail\InactivityWarningMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

test('it anonymizes graduated stockholmscience users in July', function () {
    Carbon::setTestNow('2025-07-01 12:00:00');

    // Graduated user (TE23 started in 2023, graduates 2026)
    // In July 2026, validClasses() returns TE26, TE25, TE24.
    $graduated = User::factory()->create([
        'email' => 'graduated@stockholmscience.se',
        'class' => 'TE23A',
    ]);

    // Current user (TE24 started in 2024, graduates 2027)
    $current = User::factory()->create([
        'email' => 'current@stockholmscience.se',
        'class' => 'TE24A',
    ]);

    // Staff user
    $staff = User::factory()->create([
        'email' => 'staff@stockholmscience.se',
        'class' => 'Personal',
    ]);

    Carbon::setTestNow('2026-07-02 12:00:00');

    artisan('app:anonymize-users');

    $graduated->refresh();
    $current->refresh();
    $staff->refresh();

    expect($graduated->isAnonymized())->toBeTrue();
    expect($current->isAnonymized())->toBeFalse();
    expect($staff->isAnonymized())->toBeFalse();
});

test('it sends warnings to inactive users with other emails after 6 months', function () {
    Mail::fake();
    Carbon::setTestNow('2026-01-01 12:00:00');

    $inactiveUser = User::factory()->create([
        'email' => 'inactive@gmail.com',
        'last_activity_at' => now()->subMonths(6)->subDay(),
    ]);

    artisan('app:anonymize-users');

    $inactiveUser->refresh();
    expect($inactiveUser->inactivity_warning_sent_at)->not->toBeNull();
    Mail::assertQueued(InactivityWarningMail::class, function ($mail) {
        return $mail->hasTo('inactive@gmail.com');
    });
});

test('it anonymizes inactive users with other emails after 7 months', function () {
    Carbon::setTestNow('2026-01-01 12:00:00');

    $veryInactiveUser = User::factory()->create([
        'email' => 'very-inactive@gmail.com',
        'last_activity_at' => now()->subMonths(7)->subDay(),
    ]);

    artisan('app:anonymize-users');

    expect($veryInactiveUser->refresh()->isAnonymized())->toBeTrue();
});

test('it resets inactivity_warning_sent_at when user becomes active', function () {
    $user = User::factory()->create([
        'last_activity_at' => now()->subMonths(6),
        'inactivity_warning_sent_at' => now()->subDays(1),
    ]);

    actingAs($user);
    get('/');

    $user->refresh();
    expect($user->inactivity_warning_sent_at)->toBeNull();
    /** @var Carbon $lastActivity */
    $lastActivity = $user->last_activity_at;
    expect($lastActivity->isToday())->toBeTrue();
});
