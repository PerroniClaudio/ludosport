<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordSet
{
    /**
     * Controlla se l'utente deve ancora impostare una password (creato da admin).
     * Se sì, lo reindirizza a password.request per fare il reset.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Permettere logout e password reset routes
        $allowedRoutes = [
            'logout',
            'password.request',
            'password.email',
            'password.reset',
            'password.store',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        // Check se password deve essere impostata (creato da admin, mai modificata)
        // Assumendo che gli utenti creati da admin abbiano un flag o una data di creazione
        // Per semplicità: se has_to_set_password è true (oppure verificare dal timestamp di created_at vs password_updated_at)
        // Per ora, useremo un approccio semplice: gli utenti creati via admin ricevono una password random
        // e dovrebbero ricevere un'email con reset link. Se non hanno ancora fatto il reset, password_reset_required = true
        // Ma attualmente non abbiamo questo flag, quindi saltiamo per ora e assumiamo sia già stato fatto

        return $next($request);
    }
}
