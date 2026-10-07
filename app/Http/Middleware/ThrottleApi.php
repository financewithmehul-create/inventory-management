<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Limits the REST API to a fixed number of calls per minute for each person (or IP address when signed out).
 */
class ThrottleApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/*', 'admin/api/*') || app()->runningUnitTests()) {
            return $next($request);
        }

        $key = 'api:'.($request->bearerToken() ? sha1($request->bearerToken()) : $request->ip());
        $limit = (int) config('app.api_rate_limit', 120);

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json(['message' => 'Too many requests.'], 429, ['Retry-After' => RateLimiter::availableIn($key)]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
