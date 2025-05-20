<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            $attendanceStatuses = [
                'done' => \App\Models\Attendance::STATUS_DONE,
            ];

            $todayAttendance = null;
            if ($user instanceof \App\Models\User) {
                $todayAttendance = $user->attendances()
                    ->whereDate('date', now()->toDateString())
                    ->first();
            }

            $view->with(compact('todayAttendance', 'attendanceStatuses'));
        });
    }
}