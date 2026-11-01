<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// 認証不要
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// 認証必要（トークンが必要）
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
