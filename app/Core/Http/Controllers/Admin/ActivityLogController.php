<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Services\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    protected ActivityLogService $activityService;

    public function __construct(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Get all activity logs
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 100);
        $type = $request->input('type');

        $activity = $this->activityService->getRecentActivity($limit, $type);

        return response()->json([
            'success' => true,
            'activity' => $activity,
        ]);
    }

    /**
     * Get recent activity
     */
    public function recent(Request $request)
    {
        $limit = $request->input('limit', 50);
        $activity = $this->activityService->getRecentActivity($limit);

        return response()->json([
            'success' => true,
            'activity' => $activity,
        ]);
    }

    /**
     * Get user-specific activity
     */
    public function userActivity(Request $request, int $userId)
    {
        $user = \App\Core\Models\User::findOrFail($userId);
        $limit = $request->input('limit', 100);

        $activity = $this->activityService->getUserActivity($user, $limit);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
            'activity' => $activity,
        ]);
    }

    /**
     * Get suspicious activity
     */
    public function suspicious()
    {
        $suspicious = $this->activityService->getSuspiciousActivity();

        return response()->json([
            'success' => true,
            'suspicious_activity' => $suspicious,
        ]);
    }

    /**
     * Clean old logs
     */
    public function clean()
    {
        $deleted = $this->activityService->cleanOldLogs();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old activity logs",
            'deleted_count' => $deleted,
        ]);
    }
}
