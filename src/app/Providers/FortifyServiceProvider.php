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
        // LoginRequestのバリデーションを使用
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(fn() => view('auth.register'));

        Fortify::loginView(function (Request $request) {
            return $request->is('admin/*')
                ? view('auth.admin-login')
                : view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $guard = $request->is('admin/*') ? 'admin' : 'web';
            $credentials = $request->only('email', 'password');

            if (Auth::guard($guard)->attempt($credentials, $request->filled('remember'))) {
                return Auth::guard($guard)->user();
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}