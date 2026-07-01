<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Controlla se l'utente ha verificato la propria email.
     * Se no, lo reindirizza alla pagina di verifica email.
     * Applica solo agli utenti auto-registrati (email_verified_at è null ma non è stato creato da admin).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Permettere solo email verification routes
        $allowedRoutes = [
            'verification.notice',
            'verification.verify',
            'verification.send',
            'password.request',
            'password.email',
            'password.reset',
            'password.store',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        return redirect(route('verification.notice', absolute: false));
    }
}
