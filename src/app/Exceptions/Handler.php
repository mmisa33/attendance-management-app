<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // 未認証のユーザーがアクセスした際の処理
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $guard = $exception->guards()[0] ?? session('last_auth_guard');

        if ($guard === 'admin') {
            return redirect()->guest(route('admin.login'));
        }

        return redirect()->guest(route('login'));
    }
}
