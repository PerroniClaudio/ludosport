<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRoleAndInstitutionSelectedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $authUser = Auth::user();
        if (! $authUser) {
            return redirect()->route('login');
        }

        $authUser = User::find($authUser->id);

        $onboardingPending = ! $authUser->hasVerifiedEmail()
            || ! $authUser->hasAcceptedLatestPrivacyPolicy()
            || ! $authUser->profile_completed
            || $authUser->isMinorPendingApproval();

        // Controllo ruolo in sessione
        $role = session('role');
        $roles = $authUser->roles;

        if (! $role) {
            if ($authUser->isMinorPendingApproval() && $authUser->hasRole('athlete')) {
                $role = 'athlete';
                session(['role' => $role]);
            } elseif ($roles->count() === 1) {
                $role = $roles->first()->label;
                session(['role' => $role]);
            } else {
                if ($onboardingPending) {
                    return $next($request);
                }

                if (! $request->routeIs('role-selector', 'profile.role.update', 'logout')) {
                    return redirect()->route('role-selector');
                }
            }
        }

        if ($onboardingPending) {
            return $next($request);
        }

        // Se il ruolo richiede istituzione
        if (in_array($role, ['rector', 'manager', 'dean'])) {
            $institution = session('institution');
            if (! $institution) {
                if (in_array($role, ['rector', 'manager'])) {
                    $primaryAcademies = $authUser->academies->where('pivot.is_primary', 1);
                    if ($primaryAcademies->count() === 1) {
                        session(['institution' => $primaryAcademies->first()]);
                    } else {
                        return redirect()->route('institution-selector');
                    }
                } elseif ($role === 'dean') {
                    $primarySchools = $authUser->schools->where('pivot.is_primary', 1);
                    if ($primarySchools->count() === 1) {
                        session(['institution' => $primarySchools->first()]);
                    } else {
                        return redirect()->route('institution-selector');
                    }
                }
            }
        }

        return $next($request);
    }
}
