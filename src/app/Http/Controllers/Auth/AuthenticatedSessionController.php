<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class AuthenticatedSessionController extends Controller
{

    // ログインページ表示
    public function create(Request $request)
    {
        return $request->is('admin/*')
            ? view('admin.auth.login') // 管理者用ログインページ
            : view('auth.login'); // 一般ユーザー用ログインページ
    }

    // ログイン処理
    public function store(LoginRequest $request)
    {
        // 管理者か一般ユーザーか判定
        $guard = $request->is('admin/*') ? 'admin' : 'web';
        $credentials = $request->only('email', 'password');

        // ログイン試行
        if (Auth::guard($guard)->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // 管理者ログインの場合、固定でリダイレクト
            if ($guard === 'admin') {
                return redirect()->route('admin.attendance.list');
            }

            // 一般ユーザーの場合
            return redirect()->route('attendance.index');
        }

        return back()
            ->withInput()
            ->withErrors(['email' => 'ログイン情報が登録されていません']);
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

    // メール認証チェック
    public function verifyCheck()
    {
        $user = Auth::user();

        // 管理者はメール認証をスキップ
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.attendance.list');
        }

        // 一般ユーザーかつメール認証が完了している場合はリダイレクト
        if ($user instanceof MustVerifyEmail && $user->hasVerifiedEmail()) {
            return redirect()->route('attendance.index');
        }

        // 認証が完了していない場合はエラーメッセージを表示
        return redirect()->route('verification.notice')
            ->with('error', 'メール認証が完了していません');
    }
}