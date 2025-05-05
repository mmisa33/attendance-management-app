<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticatedSessionController extends Controller
{
    // 管理者用ログインページ表示
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    // ログイン処理（管理者）
    public function store(Request $request)
    {
        $formRequest = new AdminLoginRequest();
        $validated = $request->validate($formRequest->rules(), $formRequest->messages());

        $credentials = $validated;
        unset($credentials['remember']);

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.attendance.list'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    // ログアウト処理（管理者）
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}