<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRoleMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response {

        if(!$request->user()->hasRole('admin')){
            $selectedRole = Role::where('name', $role)->first();
            $user = $selectedRole->users()->where('user_id', $request->user()->id)->first();
            if (!$user) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
