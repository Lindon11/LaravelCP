<?php

namespace App\Core\Middleware;

use App\Core\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;

class VerifyLicense
{
    /**
     * Routes that should be excluded from license checking.
     */
    protected array $except = [
        'install/*',
        'admin/login',
        'admin/install/*',
        'api/auth/login',
        'api/auth/register',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip license check for excluded routes
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // Skip if not installed yet
        if (!file_exists(storage_path('installed'))) {
            return $next($request);
        }

        // Check license
        if (!LicenseService::isLicensed()) {
            // For API requests, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'unlicensed',
                    'message' => 'This installation requires a valid LaravelCP license. Please contact the administrator.',
                ], 403);
            }

            // For web requests to admin, let them through to see the license warning
            // The admin panel will show a license activation prompt
            return $next($request);
        }

        return $next($request);
    }
}
