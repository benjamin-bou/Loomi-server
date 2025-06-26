<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ArticleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);

Route::get('/boxes', [BoxController::class, 'index']);
Route::get('/boxes/{id}', [BoxController::class, 'show']);

Route::get('gift-cards', [GiftCardController::class, 'index']);
Route::post('gift-cards/activate', [GiftCardController::class, 'activate']);
Route::get('payment-methods', [OrderController::class, 'getPaymentMethods']);

Route::get('/boxes/{id}/reviews', [ReviewController::class, 'getBoxReviews']);
Route::get('/subscriptions/{id}/reviews', [ReviewController::class, 'getSubscriptionReviews']);

// Routes pour les articles (blog)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'me']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/profile/deliveries', [DeliveryController::class, 'getUserDeliveries']);
    Route::get('/my-subscription', [SubscriptionController::class, 'current']);
    Route::post('/cancel-subscription', [SubscriptionController::class, 'cancel']);
    Route::get('informations', [AuthController::class, 'me']);

    Route::post('/order', [OrderController::class, 'store']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);

    Route::get('/my-gift-cards', [GiftCardController::class, 'getUserGiftCards']);

    // Routes pour la gestion des livraisons d'abonnement
    Route::post('/subscription-deliveries', [DeliveryController::class, 'addSubscriptionDelivery']);
    Route::patch('/subscription-deliveries/{id}/delivered', [DeliveryController::class, 'markAsDelivered']);

    Route::get('/admin/boxes', [BoxController::class, 'adminIndex']);
    Route::put('/admin/boxes/{id}', [BoxController::class, 'update']);

    // Routes pour les avis
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/user/{boxId}', [ReviewController::class, 'getUserReview']);
    Route::get('/reviews/user/subscription/{subscriptionTypeId}', [ReviewController::class, 'getUserSubscriptionReview']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});
