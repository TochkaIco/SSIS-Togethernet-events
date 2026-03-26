<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::view('/', 'homepage')->name('home');
Route::view('/events', 'events')->name('events');
Route::view('/faq', 'faq')->name('faq');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'livewire.admin.dashboard')->name('admin.dashboard');
    Route::get('/admin/events', [EventController::class, 'index'])->name('admin.events');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/auth/redirect', function () {
        return Socialite::driver('google')->redirect();
    })->name('auth.google');
    Route::get('/api/auth/callback/google', [OAuthController::class, 'store'])->name('login.oauth');
});

require __DIR__.'/settings.php';
