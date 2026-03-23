<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCookiePolicyAccepted
{
    /**
     * Handle an incoming request.
     * 
     * NOTA: I cookie policy si gestiscono completamente lato client via localStorage e banner.
     * Questo middleware non fa nulla e permette sempre il passaggio.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // I cookie policy si gestiscono lato client, non c'è bisogno di bloccare l'accesso
        return $next($request);
    }
}
