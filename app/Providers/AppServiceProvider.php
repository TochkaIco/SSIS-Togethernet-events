<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::before(function ($user): ?true {
            if ($user->hasRole(['super-admin', 'maintainer'])) {
                return true;
            }

            return null;
        });

        Gate::define('admin', function ($user) {
            return $user->hasRole('admin');
        });

        Event::listen(Authenticated::class, function ($event) {
            $maintainerEmail = config('app.dev_info.maintainer_email', '');

            // Check if this is the person from .env and if they lack the role
            if ($event->user->email === $maintainerEmail && ! $event->user->hasRole('maintainer')) {
                $event->user->assignRole('maintainer');
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
