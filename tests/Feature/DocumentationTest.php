<?php

declare(strict_types=1);

use App\Livewire\Documentation;
use Livewire\Livewire;

it('can render documentation page', function () {
    Livewire::test(Documentation::class)
        ->assertStatus(200)
        ->assertSee('Architecture'); // ARCHITECTURE.md should be the default
});

it('can render a specific documentation page', function () {
    Livewire::test(Documentation::class, ['page' => 'DEVELOPMENT'])
        ->assertStatus(200)
        ->assertSee('Development');
});

it('throws 404 for non-existent page', function () {
    Livewire::test(Documentation::class, ['page' => 'NON_EXISTENT'])
        ->assertStatus(404);
});
