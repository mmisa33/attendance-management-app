<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MultiGuardSessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ガードに基づいてセッションクッキー名を設定
        $guard = $request->is('admin/*') ? 'admin' : 'web';
        $sessionCookie = $guard === 'admin' ? env('SESSION_COOKIE_ADMIN', 'admin_session') : env('SESSION_COOKIE_USER', 'web_session');

        // セッションクッキー名を設定
        config(['session.cookie' => $sessionCookie]);

        return $next($request);
    }
}