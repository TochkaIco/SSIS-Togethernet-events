<?php

declare(strict_types=1);

use App\Livewire\Settings\DeleteUserForm;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('user can delete their account by typing DELETE', function () {
    Http::fake();

    $user = User::factory()->create([
        'elevkar_token' => 'some-token',
    ]);
    $this->actingAs($user);

    Livewire::test(DeleteUserForm::class)
        ->set('confirmation', 'DELETE')
        ->call('deleteUser')
        ->assertRedirect('/');

    expect(User::find($user->id))->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('user cannot delete their account without correct confirmation', function () {
    Http::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(DeleteUserForm::class)
        ->set('confirmation', 'WRONG')
        ->call('deleteUser')
        ->assertHasErrors(['confirmation']);

    expect(User::find($user->id))->not->toBeNull();
    expect(auth()->check())->toBeTrue();
});
