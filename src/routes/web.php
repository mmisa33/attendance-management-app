<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

// ログアウト処理
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ログインユーザー専用ページ
Route::middleware(['auth'])->group(function () {

    // 出勤管理ページ
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

});
