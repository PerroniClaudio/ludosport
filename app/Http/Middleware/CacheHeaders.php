<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $maxAge = '900'): Response {
        $response = $next($request);

        // Solo per GET requests di successo
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $response->headers->set('Cache-Control', "public, max-age={$maxAge}");
            $response->headers->set('Vary', 'Accept-Encoding');

            // Aggiungi ETag per cache validation
            $etag = md5($response->getContent());
            $response->headers->set('ETag', "\"{$etag}\"");

            // Check if client has cached version
            if ($request->headers->get('If-None-Match') === "\"{$etag}\"") {
                return response('', 304)->withHeaders($response->headers->all());
            }
        }

        return $response;
    }
}
