<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'me']);
    Route::get('/orders', [OrderController::class, 'index']);
    // Route::get('/subscription', [SubscriptionController::class, 'index']);

    Route::get('/admin/boxes', [BoxController::class, 'adminIndex']);
    Route::put('/admin/boxes/{id}', [BoxController::class, 'update']);
});
