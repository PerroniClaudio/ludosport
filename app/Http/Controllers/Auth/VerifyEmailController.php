<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Se non è loggato deve rimandarlo al login e fare in modo che quando si logga effettui la verifica (reindirizzi a questa pagina)

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            if ($request->user()->has_to_switch_from_minor) {
                $request->user()->forceFill([
                    'is_user_minor' => false,
                    'has_to_switch_from_minor' => false,
                    'has_admin_approved_minor' => false,
                ])->save();
            }

            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
