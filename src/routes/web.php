<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ログアウト処理
Route::post('/logout', [AuthController::class, 'logout']);