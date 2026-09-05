<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// Admin authentication routes
Route::prefix('admin_panel')->name('admin.')->group(function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    });

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Tenants
        Route::get('/tenants', [AdminDashboardController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/create', [AdminDashboardController::class, 'tenantCreate'])->name('tenants.create');
        Route::post('/tenants', [AdminDashboardController::class, 'tenantStore'])->name('tenants.store');
        Route::get('/tenants/{id}', [AdminDashboardController::class, 'tenantShow'])->name('tenants.show');
        Route::patch('/tenants/{id}/status', [AdminDashboardController::class, 'tenantUpdateStatus'])->name('tenants.status');

        // API Control
        Route::get('/api-control', [AdminDashboardController::class, 'apiControl'])->name('api-control');
        
        // WhatsApp Health & Sessions
        Route::get('/whatsapp', [AdminDashboardController::class, 'whatsappHealth'])->name('whatsapp');
        Route::post('/whatsapp/{id}/restart', [AdminDashboardController::class, 'whatsappRestartInstance'])->name('whatsapp.restart');
        
        // Audit Logs
        Route::get('/logs', [AdminDashboardController::class, 'logs'])->name('logs');
        
        // System Settings
        Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminDashboardController::class, 'settingsUpdate'])->name('settings.update');

        // User Management
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/logs', [\App\Http\Controllers\Admin\AdminUserController::class, 'logs'])->name('users.logs');
        Route::post('/users/{user}/toggle', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggle');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');

        // Profile Routes
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/security', [\App\Http\Controllers\Admin\ProfileController::class, 'updateSecurity'])->name('profile.security');
    });
});

