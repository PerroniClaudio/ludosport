<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MinorSwitchController extends Controller
{
    public function edit(Request $request): RedirectResponse|View
    {
        if (!$request->user()->has_to_switch_from_minor) {
            return redirect()->route('dashboard');
        }

        return view('auth.minor-switch-email', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->has_to_switch_from_minor) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill([
            'email' => strtolower($validated['email']),
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('status', 'adult-verification-link-sent');
    }
}
