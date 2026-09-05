<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\TenantAuthController;
use App\Http\Controllers\TenantDashboardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/lang/{locale}', [\App\Http\Controllers\LocaleController::class, 'set'])->name('lang.set');
Route::get('/', [PublicController::class, 'landing'])->name('home');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');

// Auth routes (guest only)
Route::middleware('guest:tenant')->group(function () {
    Route::get('/register', [TenantAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [TenantAuthController::class, 'register'])->name('register.post');
    Route::get('/login', [TenantAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [TenantAuthController::class, 'login'])->name('login.post');
});

// Protected routes (tenant only)
Route::middleware('auth.tenant')->group(function () {
    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
    
    // API Keys
    Route::get('/dashboard/api-keys', [TenantDashboardController::class, 'apiKeys'])->name('tenant.api-keys');
    Route::post('/dashboard/api-keys', [TenantDashboardController::class, 'generateApiKey'])->name('tenant.api-keys.generate');
    Route::delete('/dashboard/api-keys/{id}', [TenantDashboardController::class, 'revokeApiKey'])->name('tenant.api-keys.revoke');

    // Webhooks
    Route::get('/dashboard/webhooks', [TenantDashboardController::class, 'webhooks'])->name('tenant.webhooks');
    Route::post('/dashboard/webhooks', [TenantDashboardController::class, 'storeWebhook'])->name('tenant.webhooks.store');
    Route::delete('/dashboard/webhooks/{webhook}', [TenantDashboardController::class, 'destroyWebhook'])->name('tenant.webhooks.destroy');

    // Logs
    Route::get('/dashboard/logs', [TenantDashboardController::class, 'logs'])->name('tenant.logs');
    Route::get('/dashboard/logs/export', [TenantDashboardController::class, 'exportLogs'])->name('tenant.logs.export');
});

