<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login.index');
        }

        $user = Auth::user();

        if ($role === 'admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized Access');
        }

        if ($role === 'astro' && !$user->isAstro()) {
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}