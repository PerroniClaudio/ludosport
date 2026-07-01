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
            'cookie-policy.show',
            'cookie-policy.info',
            'logout',
            'verification.notice',
            'verification.verify',
            'verification.send',
        ];

        if ($request->routeIs($allowedRoutes)) {
            return $next($request);
        }

        $policy = PrivacyPolicy::getOrCreate();

        if (Auth::check() && ! Auth::user()->hasAcceptedLatestPrivacyPolicy()) {
            if (Auth::user()->isMinorPendingApproval()) {
                return $next($request);
            }

            if (! session()->has('privacy_policy_redirect_to') && $request->method() === 'GET' && ! $request->expectsJson()) {
                session(['privacy_policy_redirect_to' => $request->getRequestUri()]);
            }

            return redirect(route('privacy-policy.show', absolute: false));
        }

        return $next($request);
    }
}
