<?php

// NOTE: This middleware is unused. Inertia is NOT installed in this API-only backend.
// It is retained for reference only. The application uses JSON API responses instead.

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests
{
    /**
     * Handle an incoming request.
     *
     * This middleware previously extended Inertia\Middleware but Inertia has been
     * removed from this API-only project. It now acts as a pass-through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
