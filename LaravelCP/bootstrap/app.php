<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Keep minimal web middleware for Filament admin only
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserRank::class, // Auto-check rank progression
        ]);

        // API middleware configuration
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Enable Sanctum stateful authentication for API
        $middleware->statefulApi();

        // Register Spatie Permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling can be added here if needed
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Configure API authentication to return JSON instead of redirecting
        $middleware->redirectGuestsTo(fn () => abort(401, 'Unauthenticated'));
    })
    ->create();
