<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class AuthEither
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
            session(['last_auth_guard' => 'admin']);
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            if (Auth::guard('web')->user()->hasVerifiedEmail()) {
                Auth::shouldUse('web');
                session(['last_auth_guard' => 'web']);
                return $next($request);
            }
        }

        throw new AuthenticationException();
    }
}