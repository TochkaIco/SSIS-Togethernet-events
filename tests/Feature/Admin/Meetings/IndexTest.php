<?php

declare(strict_types=1);

use App\Livewire\Admin\Meetings\Index;
use App\Models\Meeting;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'tog-member']);
});

test('admin meetings index page can be rendered', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $meeting = Meeting::factory()->create();

    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertSee($meeting->title);
});

test('unauthorized users cannot create a meeting', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('createMeeting')
        ->assertForbidden();
});

test('tog members can create a meeting', function () {
    $user = User::factory()->create();
    $user->assignRole('tog-member');
    $this->actingAs($user);

    $response = Livewire::test(Index::class)
        ->call('createMeeting');

    $meeting = Meeting::latest()->first();
    expect($meeting)->not->toBeNull();

    $response->assertRedirect(route('admin.meetings.show', $meeting));
});
