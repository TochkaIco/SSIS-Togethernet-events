<?php

declare(strict_types=1);

use App\Models\GlobalLog;
use App\Models\User;

use function Pest\Laravel\artisan;

test('it resets TOS acceptance for all non-anonymized users when confirmed', function () {
    $user1 = User::factory()->create([
        'tos_accepted_at' => now(),
        'tos_warning_sent_at' => now(),
    ]);

    $user2 = User::factory()->create([
        'tos_accepted_at' => now(),
        'tos_warning_sent_at' => now(),
    ]);

    // Create an anonymized user to ensure they are NOT reset
    $anonymizedUser = User::factory()->create([
        'tos_accepted_at' => now(),
        'tos_warning_sent_at' => now(),
        'name' => 'Anonymized User',
        'email' => 'anonymized-'.uniqid().'@example.com',
    ]);
    $anonymizedUser->anonymize(); // This sets name, email to anonymized, etc. Let's make sure we check anonymized state.
    // Wait, let's see how notAnonymized is defined or if we can just set it.
    // Let's check User model for notAnonymized scope.

    artisan('app:reset-tos')
        ->expectsConfirmation('This will force all users to accept the Terms of Service again. Do you want to continue?', 'yes')
        ->expectsOutput('Successfully reset TOS status for 2 users.')
        ->expectsOutput('Users will be notified by the scheduled notify-tos-update command and prompted upon login.')
        ->assertExitCode(0);

    $user1->refresh();
    $user2->refresh();

    expect($user1->tos_accepted_at)->toBeNull()
        ->and($user1->tos_warning_sent_at)->toBeNull()
        ->and($user2->tos_accepted_at)->toBeNull()
        ->and($user2->tos_warning_sent_at)->toBeNull();

    expect(GlobalLog::where('action_title', 'TOS has been updated, users will be notified soon')->exists())->toBeTrue();
});

test('it does not reset TOS acceptance when not confirmed', function () {
    $user = User::factory()->create([
        'tos_accepted_at' => now(),
        'tos_warning_sent_at' => now(),
    ]);

    artisan('app:reset-tos')
        ->expectsConfirmation('This will force all users to accept the Terms of Service again. Do you want to continue?', 'no')
        ->assertExitCode(0);

    $user->refresh();
    expect($user->tos_accepted_at)->not->toBeNull()
        ->and($user->tos_warning_sent_at)->not->toBeNull();

    expect(GlobalLog::where('action_title', 'TOS has been updated, users will be notified soon')->exists())->toBeFalse();
});
