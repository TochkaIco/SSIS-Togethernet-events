<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-superadmin {email}', function (string $email) {

    $user = User::where('email', $email)->first();
    if (! $user) {
        $this->error("Det finns ingen användare med användarnamnet '$email'!");

        return;
    }

    $guard = config('auth.defaults.guard');
    $adminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);

    if ($user->hasRole('admin')) {
        $this->error('Användaren har redan super-admin rollen!');

        return;
    }

    $user->assignRole($adminRole);

    $this->info("Lade till super-admin rollen på användare $user->name ($email)");
})->purpose('Lägg till admin rollen och en användare som ska få super-admin rollen');
