<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::twoFactorAuthentication());
});

test('two factor challenge redirects to login when not authenticated', function () {
    $response = $this->get(route('two-factor.login'));

    $response->assertRedirect(route('login'));
});

test('two factor challenge can be rendered', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->withSession(['login.id' => $user->id])
        ->get(route('two-factor.login'));

    $response->assertStatus(200)
        ->assertViewIs('livewire.auth.two-factor-challenge');
});
