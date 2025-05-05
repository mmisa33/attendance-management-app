<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\User\AttendanceController  as UserAttendanceController;
use App\Http\Controllers\User\StampCorrectionRequestController;

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;

use App\Http\Controllers\Shared\AttendanceDetailController;

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
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
});

// 管理者専用ページ
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'adminAttendanceList'])->name('admin.attendance.list');
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'attendanceList'])->name('admin.attendance.staff');
});

// 一般ユーザーと管理者の両方がアクセス可能
Route::middleware(['auth.either'])->group(function () {
    // 勤怠詳細ページ
    Route::get('/attendance/{attendance}', [AttendanceDetailController::class, 'show'])->name('attendance.details');
    Route::post('/attendance/{attendance}/update', [AttendanceDetailController::class, 'updateDetail'])->name('attendance.updateDetail');
});