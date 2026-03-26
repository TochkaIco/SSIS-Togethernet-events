<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('login'));
});

test('tog members can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $response = $this->get(route('admin.dashboard'));
    $response->assertOk();
});
