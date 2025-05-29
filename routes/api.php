<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DeliveryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);

Route::get('gift-cards', [GiftCardController::class, 'index']);
Route::get('payment-methods', [OrderController::class, 'getPaymentMethods']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'me']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/profile/deliveries', [DeliveryController::class, 'getUserDeliveries']);
    Route::get('/my-subscription', [SubscriptionController::class, 'current']);
    Route::post('/cancel-subscription', [SubscriptionController::class, 'cancel']);
    Route::get('informations', [AuthController::class, 'me']);
    Route::post('gift-cards/activate', [GiftCardController::class, 'activate']);

    Route::post('/order', [OrderController::class, 'store']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);

    Route::get('/my-gift-cards', [GiftCardController::class, 'getUserGiftCards']);

    // Routes pour la gestion des livraisons d'abonnement
    Route::post('/subscription-deliveries', [DeliveryController::class, 'addSubscriptionDelivery']);
    Route::patch('/subscription-deliveries/{id}/delivered', [DeliveryController::class, 'markAsDelivered']);

    Route::get('/admin/boxes', [BoxController::class, 'adminIndex']);
    Route::put('/admin/boxes/{id}', [BoxController::class, 'update']);
});
