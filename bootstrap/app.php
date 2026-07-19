<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsTeacher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'teacher' => EnsureUserIsTeacher::class,
            'subscription' => EnsureActiveSubscription::class,
            'module' => EnsureModuleAccess::class,
        ]);

        // PayMongo posts webhooks server-to-server (no CSRF token).
        $middleware->validateCsrfTokens(except: [
            'subscription/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
