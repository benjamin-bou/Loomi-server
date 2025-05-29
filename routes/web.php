<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/boxes', [BoxController::class, 'index']);
Route::get('/api/boxes/{id}', [BoxController::class, 'show']);
