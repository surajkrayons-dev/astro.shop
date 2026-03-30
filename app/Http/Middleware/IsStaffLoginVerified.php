<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsStaffLoginVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()->getName();

        $whitelist_routes = [
            'admin.dashboard.login.requested',
            'admin.dashboard.login.request.status'
        ];

        if (auth()->check() && !auth()->user()->isAstro() && !auth()->user()->isSuperAdmin() && ! auth()->user()->isUser() &&  $route != 'admin.profile.logout') {
            $login_request_status = session()->get('login_request_status');
            $login_request_hash = session()->get('login_request_hash');

            if (!$login_request_status && !$login_request_hash) {
                auth()->logout();
                return redirect()->route('admin.login.index');
            } elseif (in_array($route, $whitelist_routes) && $login_request_status == 'verified') {
                return redirect()->route('admin.dashboard.index');
            } elseif ($route == 'admin.dashboard.login.requested' && $login_request_status == 'pending') {
                $status = \App\Models\LoginRequest::where('hash_token', $login_request_hash)->value('status');

                if ($status == 'rejected') {
                    auth()->logout();
                    return redirect()->route('admin.login.index');
                } elseif ($status == 'verified') {
                    session()->put('login_request_status', 'verified');
                    return redirect()->route('admin.dashboard.index');
                }
            } elseif (!in_array($route, $whitelist_routes) && $login_request_status != 'verified') {
                return redirect()->route('admin.dashboard.login.requested');
            } else {
                $status = \App\Models\LoginRequest::where('hash_token', $login_request_hash)->value('status');
                if ($status == 'logged_out') {
                    auth()->logout();
                    return redirect()->route('admin.login.index');
                }
            }
        }

        return $next($request);
    }
}