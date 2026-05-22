<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTosAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() &&
            ! $request->user()->tos_accepted_at &&
            ! $request->routeIs('terms.accept')) {
            return $request->expectsJson()
                ? abort(403, 'You must accept the Terms of Service.')
                : redirect()->route('terms.accept');
        }

        return $next($request);
    }
}
