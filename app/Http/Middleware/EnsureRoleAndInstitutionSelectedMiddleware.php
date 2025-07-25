<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EnsureRoleAndInstitutionSelectedMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Controllo ruolo in sessione
        $role = session('role');
        $roles = $authUser->roles;

        if (!$role) {
            if ($roles->count() === 1) {
                $role = $roles->first()->label;
                session(['role' => $role]);
            } else {
                return redirect()->intended(route('role-selector', absolute: false));
            }
        }

        // Se il ruolo richiede istituzione
        if (in_array($role, ['rector', 'manager', 'dean'])) {
            $institution = session('institution');
            if (!$institution) {
                if (in_array($role, ['rector', 'manager'])) {
                    $primaryAcademies = $authUser->academies->where('pivot.is_primary', 1);
                    if ($primaryAcademies->count() === 1) {
                        session(['institution' => $primaryAcademies->first()]);
                    } else {
                        return redirect()->intended(route('institution-selector', absolute: false));
                    }
                } elseif ($role === 'dean') {
                    $primarySchools = $authUser->schools->where('pivot.is_primary', 1);
                    if ($primarySchools->count() === 1) {
                        session(['institution' => $primarySchools->first()]);
                    } else {
                        return redirect()->intended(route('institution-selector', absolute: false));
                    }
                }
            }
        }

        return $next($request);
    }
}
