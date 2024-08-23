<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRoleMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response {

        if(!auth()->check()){
            return redirect()->route('login')->with('error', 'You need to login to access this page');
        }
        $authRole = User::find(auth()->user()->id)->getRole();
        if(!in_array($authRole, $roles)){
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
