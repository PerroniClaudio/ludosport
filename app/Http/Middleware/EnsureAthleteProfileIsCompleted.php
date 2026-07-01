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

        if (!$user || $user->profile_completed || !$user->hasRole('athlete')) {
            return $next($request);
        }

        $allowedRoutes = [
            'profile.edit',
            'profile.update',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('profile.edit')->with('error', 'Complete your profile before continuing.');
    }
}
