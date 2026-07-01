<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAthleteProfileIsCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->profile_completed || ! $user->hasRole('athlete') || $user->isMinorPendingApproval()) {
            return $next($request);
        }

        $allowedRoutes = [
            'profile.edit',
            'profile.update',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect(route('profile.edit', absolute: false))->with('error', 'Complete your profile before continuing.');
    }
}
