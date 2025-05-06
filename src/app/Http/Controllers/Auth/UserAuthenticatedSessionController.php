<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAuthenticatedSessionController extends Controller
{
    // ログインページ表示（一般ユーザー）★必要か確認
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ログイン処理（一般ユーザー）★必要か確認
    public function store(LoginRequest $request)
    {
        $credentials = $request->safe()->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('attendance.index'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    // ログアウト処理（一般ユーザー）
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}