<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthEither
{
    public function handle(Request $request, Closure $next)
    {
        // 管理者または一般ユーザーがログインしている場合
        if (Auth::guard('admin')->check() || Auth::guard('web')->check()) {
            return $next($request);
        }

        // 未ログイン時、対象に応じたログインページへ
        return $request->is('admin/*') || $request->routeIs('admin.*')
            ? redirect()->route('admin.login')
            : redirect()->route('login');
    }
}