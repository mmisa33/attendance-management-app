<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{

    // ログインページ表示
    public function create(Request $request)
    {
        return $request->is('admin/*')
            ? view('admin.auth.login')    // 管理者用ログインページ
            : view('auth.login');         // 一般ユーザー用ログインページ
    }

    // ログイン処理
    public function store(LoginRequest $request)
    {
        $guard = $request->is('admin/*') ? 'admin' : 'web';
        $credentials = $request->only('email', 'password');

        if (Auth::guard($guard)->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // ログイン後のリダイレクト処理
            return $guard === 'admin'
                ? redirect()->route('admin.attendance.list') // 管理者用ページ
                : redirect()->route('attendance.index'); // ユーザー用ページ
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    // ログアウト処理
    public function destroy(Request $request)
    {
        // 現在のURLを確認しガードを判定
        if ($request->is('admin/*')) {
            Auth::guard('admin')->logout();
            $redirectRoute = 'admin.login';
        } else {
            Auth::guard('web')->logout();
            $redirectRoute = 'login';
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}