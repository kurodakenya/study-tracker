<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\StudyLogController;
use Illuminate\Support\Facades\Route;

// 認証不要
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// 認証必要（トークンが必要）
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // 資格 CRUD
    Route::apiResource('certifications', CertificationController::class);

    // カテゴリ（資格にネスト：一覧・作成）
    Route::get('certifications/{certification}/categories', [CategoryController::class, 'index']);
    Route::post('certifications/{certification}/categories', [CategoryController::class, 'store']);

    // カテゴリ（単体：更新・削除）
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    // 学習記録 CRUD
    Route::apiResource('study-logs', StudyLogController::class)->except(['show']);
});
