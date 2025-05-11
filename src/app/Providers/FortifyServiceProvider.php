<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
    }

    public function boot(): void
    {
        // ユーザー登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // ユーザー登録ページの表示
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // メール認証ページの表示
        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });

        // ログインページの表示
        Fortify::loginView(function (Request $request) {
            return $request->is('admin/*')
                ? view('admin.auth.login')
                : view('auth.login');
        });

        // ログイン認証処理
        Fortify::authenticateUsing(function (Request $request) {
            $guard = $request->is('admin/*') ? 'admin' : 'web';
            $credentials = $request->only('email', 'password');

            if (Auth::guard($guard)->attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();
                return Auth::guard($guard)->user();
            }

            return null;
        });

        // ログイン試行のレート制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}