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
            'verification.notice',
            'verification.verify',
            'verification.send',
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
            if (Auth::user()->isMinorPendingApproval()) {
                return $next($request);
            }

            session(['privacy_policy_redirect_to' => $request->getRequestUri()]);

            return redirect(route('privacy-policy.show', absolute: false));
        }

        return $next($request);
    }
}
