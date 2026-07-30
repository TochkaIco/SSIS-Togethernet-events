<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('user can pull picture from Google', function () {
    $user = User::factory()->create([
        'profile_picture' => 'https://example.com/avatar.jpg',
    ]);
    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->call('pullPictureFromGoogle')
        ->assertRedirect(route('login'));

    expect($user->fresh()->profile_picture)->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('guest is redirected from profile page', function () {
    $this->get('/settings/profile')
        ->assertRedirect(route('login'));
});
