<?php

use App\Http\Controllers\Api\TripayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook Callback Tripay (harus bebas CSRF)
Route::post('/webhooks/tripay', [TripayWebhookController::class, 'handle'])->name('api.webhooks.tripay');
