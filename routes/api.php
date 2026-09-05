<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// Secure WhatsApp Webhooks (Multi-provider support)
Route::middleware(['throttle:100,1'])->group(function () {
    Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
    Route::match(['get', 'post'], '/whatsapp/webhook/{provider}', [WhatsAppWebhookController::class, 'handle']);
});

// General RAG Chat API (Sanctum)
Route::middleware('auth:sanctum')->post('/chat/ask', [ChatController::class, 'ask']);

