<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KnowledgeSourceController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth.tenant'])->group(function () {
    // WhatsApp Conversations & Live Chat
    Route::get('/api/conversations', [\App\Http\Controllers\Dashboard\DashboardController::class, 'conversations'])->name('api.conversations');
    Route::post('/api/conversations/{conversation}/reply', [\App\Http\Controllers\Dashboard\DashboardController::class, 'reply'])->name('api.conversations.reply');
    Route::post('/api/conversations/{conversation}/toggle-escalation', [\App\Http\Controllers\Dashboard\DashboardController::class, 'toggleEscalation'])->name('api.conversations.toggle-escalation');
    Route::get('/api/human-requests', [\App\Http\Controllers\Dashboard\DashboardController::class, 'humanRequests'])->name('api.human-requests');
    Route::get('/api/pending-alerts', [\App\Http\Controllers\Dashboard\DashboardController::class, 'pendingAlerts'])->name('api.pending-alerts');
    Route::post('/api/pending-alerts/{id}/ignore', [\App\Http\Controllers\Dashboard\DashboardController::class, 'ignoreAlert'])->name('api.pending-alerts.ignore');
    Route::post('/api/pending-alerts/{id}/confirm', [\App\Http\Controllers\Dashboard\DashboardController::class, 'confirmAlert'])->name('api.pending-alerts.confirm');
    Route::get('/api/analytics', [\App\Http\Controllers\Dashboard\DashboardController::class, 'analytics'])->name('api.analytics');
    Route::get('/dashboard/conversations', [\App\Http\Controllers\Dashboard\DashboardController::class, 'conversationsView'])->name('conversations.index');

    // RAG Knowledge Base & System Settings
    Route::get('/dashboard/knowledge', [KnowledgeSourceController::class, 'index'])->name('knowledge.index');
    Route::post('/dashboard/knowledge', [KnowledgeSourceController::class, 'store'])->name('knowledge.store');
    Route::delete('/dashboard/knowledge/{knowledge_source}', [KnowledgeSourceController::class, 'destroy'])->name('knowledge.destroy');
    Route::get('/dashboard/system-settings', [\App\Http\Controllers\Dashboard\DashboardController::class, 'knowledgeConfigView'])->name('dashboard.system-settings');
    Route::get('/dashboard/knowledge-config', fn () => redirect()->route('dashboard.system-settings'))->name('dashboard.knowledge-config');
    Route::post('/dashboard/system-settings', [\App\Http\Controllers\Dashboard\DashboardController::class, 'updateKnowledgeConfig'])->name('dashboard.system-settings.update');
    Route::post('/dashboard/knowledge-config', [\App\Http\Controllers\Dashboard\DashboardController::class, 'updateKnowledgeConfig'])->name('dashboard.knowledge-config.update');

    // Embeddable Chat Widget Management
    Route::get('/dashboard/widget', [\App\Http\Controllers\Dashboard\WidgetController::class, 'index'])->name('widget.index');
    Route::post('/dashboard/widget', [\App\Http\Controllers\Dashboard\WidgetController::class, 'update'])->name('widget.update');

    // WhatsApp Session Management
    Route::get('/dashboard/whatsapp', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'index'])->name('whatsapp.index');
    Route::get('/dashboard/whatsapp/instance', fn () => redirect()->route('whatsapp.index'));
    Route::post('/dashboard/whatsapp/instance', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'storeInstance'])
        ->middleware('whatsapp.limit')->name('whatsapp.instance.store');
    Route::post('/dashboard/whatsapp/instance/{instance}/toggle', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'toggleInstance'])->name('whatsapp.instance.toggle');
    Route::get('/dashboard/whatsapp/instance/{instance}/qr', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'getQr'])->name('whatsapp.instance.qr');
    Route::get('/dashboard/whatsapp/instance/{instance}/status', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'getStatus'])->name('whatsapp.instance.status');
    Route::delete('/dashboard/whatsapp/instance/{instance}', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'deleteInstance'])->name('whatsapp.instance.delete');
    Route::get('/dashboard/whatsapp/template', fn () => redirect()->route('whatsapp.index'));
    Route::post('/dashboard/whatsapp/template', [\App\Http\Controllers\Dashboard\WhatsappController::class, 'storeTemplate'])->name('whatsapp.template.store');

    // User Management
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/dashboard/users', [\App\Http\Controllers\Dashboard\TenantUserController::class, 'index'])->name('tenant.users.index');
    });

    Route::middleware(['permission:users.manage'])->group(function () {
        Route::post('/dashboard/users', [\App\Http\Controllers\Dashboard\TenantUserController::class, 'store'])->name('tenant.users.store');
        Route::post('/dashboard/users/{user}/toggle', [\App\Http\Controllers\Dashboard\TenantUserController::class, 'toggleStatus'])->name('tenant.users.toggle');
        Route::post('/dashboard/users/{user}/role', [\App\Http\Controllers\Dashboard\TenantUserController::class, 'updateRole'])->name('tenant.users.role');
    });

    // Roles & Access Control
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/dashboard/roles', [\App\Http\Controllers\Dashboard\RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware(['permission:settings.manage'])->group(function () {
        Route::get('/dashboard/roles/create', [\App\Http\Controllers\Dashboard\RoleController::class, 'create'])->name('roles.create');
        Route::post('/dashboard/roles', [\App\Http\Controllers\Dashboard\RoleController::class, 'store'])->name('roles.store');
        Route::get('/dashboard/roles/{role}/edit', [\App\Http\Controllers\Dashboard\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/dashboard/roles/{role}', [\App\Http\Controllers\Dashboard\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/dashboard/roles/{role}', [\App\Http\Controllers\Dashboard\RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Profile Routes
    Route::get('/dashboard/profile', [\App\Http\Controllers\Dashboard\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/dashboard/profile', [\App\Http\Controllers\Dashboard\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/dashboard/profile/security', [\App\Http\Controllers\Dashboard\ProfileController::class, 'updateSecurity'])->name('profile.security');
});

// Demo login for testing
Route::get('/demo-login/{id}', function ($id) {
    Auth::login(User::findOrFail($id));
    return redirect()->route('knowledge.index');
})->name('demo-login');

