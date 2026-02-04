<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRank
{
    /**
     * Handle an incoming request.
     * Automatically checks and updates user rank based on experience (V2-style)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Check if user should rank up
            $request->user()->checkRank();
            
            // Update last_active timestamp
            $request->user()->update(['last_active' => now()]);
        }

        return $next($request);
    }
}
