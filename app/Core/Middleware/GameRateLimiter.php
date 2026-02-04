<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class GameRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limit = '60:1'): Response
    {
        [$maxAttempts, $decayMinutes] = explode(':', $limit);
        
        $key = $this->resolveRequestKey($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => "Too many requests. Please wait {$seconds} seconds before trying again.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
        ]);

        return $response;
    }

    /**
     * Resolve the rate limit key for the request
     */
    protected function resolveRequestKey(Request $request): string
    {
        if ($user = $request->user()) {
            return 'game-api:' . $user->id . ':' . $request->route()->getName();
        }

        return 'game-api:' . $request->ip() . ':' . $request->route()->getName();
    }
}
