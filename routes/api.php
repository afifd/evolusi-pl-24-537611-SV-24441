<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TugasController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tugas CRUD API
    Route::apiResource('tugas', TugasController::class)->parameters([
        'tugas' => 'id',
    ]);
});
