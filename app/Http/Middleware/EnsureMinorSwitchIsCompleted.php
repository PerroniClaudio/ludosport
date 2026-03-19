<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMinorSwitchIsCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->has_to_switch_from_minor) {
            return $next($request);
        }

        $allowedRoutes = [
            'minor-switch.edit',
            'minor-switch.update',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('minor-switch.edit')->with('error', 'You must confirm your adult account email before continuing.');
    }
}
