<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchThrottleMiddleware {

    protected $limiter;

    public function __construct(RateLimiter $limiter) {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttemps = 60, $decayMinutes = 1): Response {

        $key = $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttemps)) {
            return response('Too Many Attempts.', Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
