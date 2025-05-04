<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MultiGuardSessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 現在ログインしているガードを優先的に判定
        if (Auth::guard('admin')->check()) {
            $guard = 'admin';
        } elseif (Auth::guard('web')->check()) {
            $guard = 'web';
        } else {
            // 未ログイン時はURL/ルート名から推測
            $guard = $request->is('admin/*') || $request->routeIs('admin.*') ? 'admin' : 'web';
        }

        // セッションクッキー名を動的に切り替え
        $sessionCookie = $guard === 'admin'
            ? env('SESSION_COOKIE_ADMIN', 'admin_session')
            : env('SESSION_COOKIE_USER', 'web_session');

        config(['session.cookie' => $sessionCookie]);

        return $next($request);
    }
}