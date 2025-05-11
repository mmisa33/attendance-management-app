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
            // 管理者の場合、verifiedをスキップ
            if (Auth::guard('admin')->check()) {
                return $next($request);
            }

            // 一般ユーザーの場合、メール認証のチェック
            if (Auth::guard('web')->user()->hasVerifiedEmail()) {
                return $next($request);
            }
        }

        // 未ログイン時、対象に応じたログインページへ
        return $request->is('admin/*') || $request->routeIs('admin.*')
            ? redirect()->route('admin.login')
            : redirect()->route('verification.notice');
    }
}