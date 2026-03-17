<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMinorUserIsApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isMinorPendingApproval()) {
            return $next($request);
        }

        $allowedRoutes = [
            'dashboard',
            'logout',
            'users.update-minor-documents',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'role-selector',
            'institution-selector',
            'profile.role.update',
            'profile.institution.update',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Your account is waiting for minor approval.');
    }
}
