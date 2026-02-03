<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends Controller
{
    /**
     * Get all notifications for the admin (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = AdminNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'notifications' => $notifications->map(fn($n) => $this->formatNotification($n)),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'unread_count' => AdminNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Get recent notifications for dropdown (limit 10).
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = AdminNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = AdminNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications->map(fn($n) => $this->formatNotification($n)),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count only.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = AdminNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notification = AdminNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = AdminNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} notifications marked as read"
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notification = AdminNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function clearRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = AdminNotification::where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} notifications deleted"
        ]);
    }

    /**
     * Send a test notification (for debugging).
     */
    public function sendTest(Request $request): JsonResponse
    {
        $user = $request->user();

        $notification = AdminNotification::notifyAdmin(
            $user->id,
            AdminNotification::TYPE_INFO,
            'Test Notification',
            'This is a test notification to verify the system is working correctly.',
            ['test' => true],
            '🧪',
            '/dashboard',
            AdminNotification::PRIORITY_NORMAL
        );

        return response()->json([
            'success' => true,
            'notification' => $this->formatNotification($notification),
            'message' => 'Test notification sent'
        ]);
    }

    /**
     * Send notification to all admins (admin only).
     */
    public function broadcast(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,task,user,system,report,ticket',
            'priority' => 'required|in:low,normal,high,urgent',
            'link' => 'nullable|string|max:255',
        ]);

        AdminNotification::notifyAllAdmins(
            $request->type,
            $request->title,
            $request->message,
            [],
            AdminNotification::getDefaultIcon($request->type),
            $request->link,
            $request->priority
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to all admins'
        ]);
    }

    /**
     * Format notification for JSON response.
     */
    private function formatNotification(AdminNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'icon' => $notification->icon,
            'link' => $notification->link,
            'data' => $notification->data,
            'priority' => $notification->priority,
            'is_read' => $notification->isRead(),
            'time_ago' => $notification->timeAgo(),
            'created_at' => $notification->created_at->toIso8601String(),
        ];
    }
}
