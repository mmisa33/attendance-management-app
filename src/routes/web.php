<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// 一般ユーザー
use App\Http\Controllers\User\AttendanceController  as UserAttendanceController;

// 管理者
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

// 共通（一般・管理者）
use App\Http\Controllers\Shared\AttendanceController as SharedAttendanceController;
use App\Http\Controllers\Shared\StampCorrectionRequestController as SharedStampCorrectionRequestController;

/*
|--------------------------------------------------------------------------
| 認証・ログイン/ログアウト処理
|--------------------------------------------------------------------------
*/
// メール認証チェック
Route::get('/verify/check', [AuthenticatedSessionController::class, 'verifyCheck'])->name('verify.check');

// 一般ユーザーログアウト
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:web')->name('logout');

// 管理者ログイン・ログアウト
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.submit');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:admin')->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| 一般ユーザー専用ページ
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web', 'verified')->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start-work', [UserAttendanceController::class, 'startWork'])->name('attendance.startWork');
    Route::post('/attendance/start-break', [UserAttendanceController::class, 'startBreak'])->name('attendance.startBreak');
    Route::post('/attendance/end-break', [UserAttendanceController::class, 'endBreak'])->name('attendance.endBreak');
    Route::post('/attendance/end-work', [UserAttendanceController::class, 'endWork'])->name('attendance.endWork');
    Route::get('/attendance/list', [UserAttendanceController::class, 'attendanceList'])->name('attendance.list');
});

/*
|--------------------------------------------------------------------------
| 管理者専用ページ
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'adminAttendanceList'])->name('admin.attendance.list');
    Route::get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])->name('admin.attendance.staff');
    Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])->name('admin.attendance.staff.csv');

    Route::get('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::post('/stamp_correction_request/approve/{attendance_correction_request}', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});

/*
|--------------------------------------------------------------------------
| 一般ユーザー・管理者共通ページ
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.either'])->group(function () {
    Route::get('/attendance/{id}', [SharedAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}/update', [SharedAttendanceController::class, 'update'])->name('attendance.update');

    Route::get('/stamp_correction_request/list', [SharedStampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
});