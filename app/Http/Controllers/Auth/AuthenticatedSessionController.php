<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller {
    /**
     * Display the login view.
     */
    public function create(): View {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse {
        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_disabled) {
            return back()->withErrors(['email' => 'This account has been disabled. Contact the administrator to enable it.']);
        }

        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();
        $user = User::find($user->id);
        $roles = $user->roles()->get();

        if ($roles->count() > 1) {
            return redirect()->intended(route('role-selector', absolute: false));
        } else {

            // Stesso codice anche in userController setUserRoleForSession
            if ($user->getRole() === 'rector' || $user->getRole() === 'manager') {

                $primaryAcademies = $user->academies->where('pivot.is_primary', 1);
                if ($primaryAcademies->count() > 1) {
                    return redirect()->intended(route('institution-selector', absolute: false));
                } else {
                    $primaryAcademy = $primaryAcademies->first();
                    if ($primaryAcademy) {
                         session(['institution' => $primaryAcademy]);
                    } else {
                        return redirect()->intended(route('institution-selector', absolute: false));
                    }
                }
            } else if ($user->getRole() === 'dean') {

                $primarySchools = $user->schools->where('pivot.is_primary', 1);
                if ($primarySchools->count() > 1) {
                    return redirect()->intended(route('institution-selector', absolute: false));
                } else {
                    $primarySchool = $primarySchools->first();
                    if ($primarySchool) {
                         session(['institution' => $primarySchool]);
                    } else {
                        return redirect()->intended(route('institution-selector', absolute: false));
                    }
                }
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
