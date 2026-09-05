<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/public.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'whatsapp.hmac' => \App\Http\Middleware\VerifyWhatsAppHMAC::class,
            'auth.tenant' => \App\Http\Middleware\TenantAuth::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'whatsapp.limit' => \App\Http\Middleware\CheckWhatsAppSessionLimit::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'tenant.context' => \App\Http\Middleware\SetTenantContext::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetTenantContext::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\UpdateLastActive::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SetTenantContext::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => 
            $request->is('admin_panel/*') || $request->is('admin-panel/*') 
                ? route('admin.login') 
                : route('login')
        );

        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
