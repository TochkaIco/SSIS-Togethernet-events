<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = 'password.confirm'): Response
    {
        if ($this->shouldConfirmPassword($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Password confirmation required.')], 423);
            }

            return redirect()->guest(route($redirectToRoute));
        }

        return $next($request);
    }

    /**
     * Determine if the user's password should be confirmed.
     */
    protected function shouldConfirmPassword(Request $request): bool
    {
        $user = $request->user();

        // Logic for Google users: If they have a google_id, we don't ask for a password confirmation.
        if ($user && $user->google_id) {
            return false;
        }

        $confirmedAt = $request->session()->get('auth.password_confirmed_at', 0);

        return (time() - $confirmedAt) > config('auth.password_timeout', 10800);
    }
}
