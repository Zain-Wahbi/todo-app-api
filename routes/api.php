<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\AdminTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth Routes 

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:auth');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:auth');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me',      [AuthController::class, 'me']);
        });
    });

    // Protected Routes 

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Tasks
        Route::apiResource('tasks', TaskController::class);
        Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Admin Routes

        Route::middleware('admin')->prefix('admin')->group(function () {

            Route::get('dashboard', [AdminDashboardController::class, 'index']);

            Route::get('users',                        [AdminUserController::class, 'index']);
            Route::get('users/{user}',                 [AdminUserController::class, 'show']);
            Route::patch('users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);
            Route::patch('users/{user}/change-role',   [AdminUserController::class, 'changeRole']);
            Route::delete('users/{user}',              [AdminUserController::class, 'destroy']);

            Route::get('tasks',           [AdminTaskController::class, 'index']);
            Route::delete('tasks/{task}', [AdminTaskController::class, 'destroy']);

        });
    });
});
