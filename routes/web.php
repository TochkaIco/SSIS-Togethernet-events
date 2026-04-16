<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\OAuthController;
use App\Livewire\Admin\AppConfigurationPage;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Events\EventShow as AdminShow;
use App\Livewire\Admin\Events\Index as AdminEventsIndex;
use App\Livewire\Admin\Events\ParticipantProfile;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\UserProfile;
use App\Livewire\Events\EventShow as PublicEventShow;
use App\Livewire\Events\Index as PublicEvents;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::view('/', 'homepage')->name('home');
Route::get('/events', PublicEvents::class)->name('events');
Route::get('/events/{event}', PublicEventShow::class)->name('event.show');
Route::view('/faq', 'faq')->name('faq');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['auth', 'can:view articles'])->group(function () {
        Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');
        Route::get('/admin/events', AdminEventsIndex::class)->name('admin.events');
        Route::get('/admin/events/{event}', AdminShow::class)->name('admin.event.show');
    });
    Route::middleware(['auth', 'can:create articles'])->group(function () {
        Route::post('/admin/events/create', [EventController::class, 'store'])->name('admin.event.store');
    });
    Route::middleware(['auth', 'can:edit articles'])->group(function () {
        Route::patch('admin/events/{event}/update', [EventController::class, 'update'])->name('admin.event.update');
        Route::delete('admin/events/{event}/destroy', [EventController::class, 'destroy'])->name('admin.event.destroy');
    });
    Route::middleware(['auth', 'can:manage users'])->group(function () {
        Route::get('/admin/users', UserManagement::class)->name('admin.users');
        Route::get('/admin/users/{user}', UserProfile::class)->name('admin.user.profile');
        Route::get('/admin/events/{event}/participants/{userId}', ParticipantProfile::class)->name('admin.event.participant.profile');
    });
    Route::get('/admin/config', AppConfigurationPage::class)
        ->middleware('can:role:admin|super-admin|maintainer')->name('admin.app.config');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/auth/redirect', function () {
        return Socialite::driver('google')->redirect();
    })->name('auth.google');
    Route::get('/api/auth/callback/google', [OAuthController::class, 'store'])->name('login.oauth');
});

require __DIR__.'/settings.php';
