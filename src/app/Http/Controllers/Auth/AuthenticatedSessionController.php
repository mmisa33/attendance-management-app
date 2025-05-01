<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // 一般ユーザーログインページ表示
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 管理者用ログインページ表示
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    // ログイン処理
    public function store(Request $request)
    {
        // ガード判定
        $guard = $request->is('admin/*') ? 'admin' : 'web';

        // リクエストに応じたバリデーション済みデータの取得
        $formRequest = $guard === 'admin' ? new AdminLoginRequest() : new LoginRequest();
        $validated = $request->validate($formRequest->rules(), $formRequest->messages());

        // 認証試行
        $credentials = $validated;
        unset($credentials['remember']); // 不要なフィールドを削除

        if (Auth::guard($guard)->attempt($credentials, $request->filled('remember'))) {
            // セッションを再生成
            $request->session()->regenerate();

            // 意図したリダイレクト先に移動
            $redirectTo = $guard === 'admin' ? route('admin.attendance.list') : route('attendance.index');
            return redirect()->intended($redirectTo);
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    // ログアウト処理
    public function destroy(Request $request)
    {
        // ガード判定
        $guard = $request->is('admin/*') ? 'admin' : 'web';

        Auth::guard($guard)->logout();
        $request->session()->forget("auth.{$guard}");
        $request->session()->regenerateToken();

        return redirect()->route($guard === 'admin' ? 'admin.login' : 'login');
    }
}