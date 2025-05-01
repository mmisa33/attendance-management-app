<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AttendanceModificationController;
use Illuminate\Support\Facades\Route;

// 一般ユーザー用ログイン
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.submit');
});

// 一般ユーザー用ログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:web')
    ->name('logout');

// 管理者用ログイン
Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.submit');
});

// 管理者用ログアウト
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:admin')
    ->name('admin.logout');

// ログインユーザー専用ページ
Route::middleware('auth:web')->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [UserAttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [UserAttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [UserAttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [UserAttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::get('/attendance/list', [UserAttendanceController::class, 'attendanceList'])->name('attendance.list');
    Route::get('/attendance/{attendance}', [UserAttendanceController::class, 'attendanceDetails'])->name('attendance.details');
    Route::post('/attendance/{attendance}/update', [UserAttendanceController::class, 'updateDetail'])->name('attendance.updateDetail');
    Route::get('/stamp_correction_request/list', [AttendanceModificationController::class, 'list'])->name('stamp_correction_request.list');
});

// 管理者専用ページ
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'adminAttendanceList'])->name('admin.attendance.list');
});