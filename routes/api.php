<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubscriptionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'me']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/my-subscription', [SubscriptionController::class, 'current']);
    Route::get('informations', [AuthController::class, 'me']);
    Route::post('/order', [OrderController::class, 'store']);


    Route::get('/admin/boxes', [BoxController::class, 'adminIndex']);
    Route::put('/admin/boxes/{id}', [BoxController::class, 'update']);
});
