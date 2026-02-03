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
        // Web middleware
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserRank::class, // Auto-check rank progression
        ]);

        // API middleware configuration
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Enable Sanctum stateful authentication for API
        $middleware->statefulApi();

        // Exclude installer routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'install/*',
        ]);

        // Register Spatie Permission middleware aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'game.throttle' => \App\Http\Middleware\GameRateLimiter::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Log all exceptions to database for admin review
        $exceptions->report(function (\Throwable $e) {
            // Skip logging for certain exception types
            $skipTypes = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Validation\ValidationException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            ];

            // Don't log if it's a skipped type
            if (!in_array(get_class($e), $skipTypes)) {
                try {
                    \App\Models\ErrorLog::logError($e, request());
                } catch (\Throwable $logError) {
                    // If logging fails, don't crash the app
                    \Log::error('Failed to log error to database: ' . $logError->getMessage());
                }
            }
        });
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Configure API authentication to return JSON instead of redirecting
        $middleware->redirectGuestsTo(fn () => abort(401, 'Unauthenticated'));
    })
    ->create();
