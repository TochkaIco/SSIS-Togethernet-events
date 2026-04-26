<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale');

        if (Auth::check()) {
            $user = Auth::user();
            $locale = $user->locale ?? $locale;

            if ($user->last_activity_at === null || $user->last_activity_at->diffInMinutes(now()) >= 5) {
                $user->update(['last_activity_at' => now()]);
            }
        } elseif (session()->has('locale')) {
            $locale = session()->get('locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
