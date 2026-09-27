<?php

use App\Http\Controllers\Api\DeployWebhookController;
use App\Http\Controllers\Api\TripayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook Callback Tripay (harus bebas CSRF)
Route::post('/webhooks/tripay', [TripayWebhookController::class, 'handle'])->name('api.webhooks.tripay');
Route::post('/tripay/callback', [TripayWebhookController::class, 'handle'])->name('api.tripay.callback');

// GitHub Auto-Deploy Webhook (harus bebas CSRF)
Route::post('/deploy', [DeployWebhookController::class, 'handle'])->name('api.deploy');
