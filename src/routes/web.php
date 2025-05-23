<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;

use App\Http\Controllers\User\AttendanceController  as UserAttendanceController;

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

use App\Http\Controllers\Shared\AttendanceController as SharedAttendanceController;
use App\Http\Controllers\Shared\StampCorrectionRequestController as SharedStampCorrectionRequestController;

use Illuminate\Support\Facades\Route;

// メール認証チェック
Route::get('/verify/check', [AuthenticatedSessionController::class, 'verifyCheck'])->name('verify.check');

// 一般ユーザーログアウト処理
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:web')
    ->name('logout');

// 管理者ログイン・ログアウト処理
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('admin.login.submit');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:admin')
        ->name('admin.logout');
});

// ログインユーザー専用ページ
Route::middleware('auth:web', 'verified')->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start-work', [UserAttendanceController::class, 'startWork'])->name('attendance.startWork');
    Route::post('/attendance/start-break', [UserAttendanceController::class, 'startBreak'])->name('attendance.startBreak');
    Route::post('/attendance/end-break', [UserAttendanceController::class, 'endBreak'])->name('attendance.endBreak');
    Route::post('/attendance/end-work', [UserAttendanceController::class, 'endWork'])->name('attendance.endWork');
    Route::get('/attendance/list', [UserAttendanceController::class, 'attendanceList'])->name('attendance.list');
});

// 管理者専用ページ
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'adminAttendanceList'])->name('admin.attendance.list');
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'attendanceList'])->name('admin.attendance.staff');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.staff.csv');
    Route::get('/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::post('/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});

// 一般ユーザーと管理者の両方がアクセス可能なページ
Route::middleware(['auth.either'])->group(function () {
    Route::get('/attendance/{id}', [SharedAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}/update', [SharedAttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/stamp_correction_request/list', [SharedStampCorrectionRequestController::class, 'list'])->name('stamp_correction_request.list');
});