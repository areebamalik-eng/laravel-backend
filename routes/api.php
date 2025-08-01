<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController; // ✅ Import

// 🔐 Auth Routes (JWT-based)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ✅ Protected Task Routes
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{id}/toggle', [TaskController::class, 'toggleDone']);

    // ✅ Protected Expense Routes
    Route::apiResource('expenses', ExpenseController::class);

    // ✅ Notification Route
    Route::get('/notifications', [NotificationController::class, 'index']);
});
