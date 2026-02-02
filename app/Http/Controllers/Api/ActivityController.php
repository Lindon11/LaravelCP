<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    protected ActivityLogService $activityService;

    public function __construct(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get current user's activity history
     */
    public function myActivity(Request $request)
    {
        $limit = $request->input('limit', 50);
        $activity = $this->activityService->getUserActivity($request->user(), $limit);

        return response()->json([
            'success' => true,
            'activity' => $activity,
        ]);
    }
}
