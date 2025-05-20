<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthEither
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            if (Auth::guard('web')->user()->hasVerifiedEmail()) {
                Auth::shouldUse('web');
                return $next($request);
            }
        }

        $lastGuard = session('last_auth_guard');
        if ($lastGuard === 'admin') {
            return redirect()->route('admin.login');
        }

        return redirect()->route('verification.notice');
    }
}