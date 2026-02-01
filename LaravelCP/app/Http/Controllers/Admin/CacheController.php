<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Clear all caches
     */
    public function clear()
    {
        $this->cacheService->clearAll();

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully',
        ]);
    }

    /**
     * Clear user-specific cache
     */
    public function clearUser(int $userId)
    {
        $this->cacheService->clearUserCache($userId);

        return response()->json([
            'success' => true,
            'message' => "User #{$userId} cache cleared successfully",
        ]);
    }

    /**
     * Warm up caches
     */
    public function warmUp()
    {
        $result = $this->cacheService->warmUp();

        return response()->json($result);
    }
}
