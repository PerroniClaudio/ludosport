<?php

namespace App\Http\Middleware;

use App\Models\PrivacyPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivacyPolicyAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedRoutes = [
            'privacy-policy.show',
            'privacy-policy.accept',
            'privacy-policy.decline',
            'logout',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        // Se la privacy policy non esiste, salta il controllo
        $policy = PrivacyPolicy::find(1);
        if (! $policy) {
            return $next($request);
        }

        if (Auth::check() && ! Auth::user()->hasAcceptedLatestPrivacyPolicy()) {
            session(['privacy_policy_redirect_to' => $request->url()]);

            return redirect(route('privacy-policy.show'));
        }

        return $next($request);
    }
}
