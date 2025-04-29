<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceModificationController;
use Illuminate\Support\Facades\Route;

// ログアウト処理
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ログインユーザー専用ページ
Route::middleware(['auth'])->group(function () {

    // 勤怠登録ページ
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // 勤怠一覧ページ
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');

    // 勤怠詳細画面
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'attendanceDetails'])->name('attendance.details');
    Route::post('/attendance/{attendance}/update', [AttendanceController::class, 'updateDetail'])->name('attendance.updateDetail');

    // 申請一覧ページ
    Route::get('/stamp_correction_request/list', [AttendanceModificationController::class, 'list'])->name('stamp_correction_request.list');
});