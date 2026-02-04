<?php

namespace App\Core\Http\Controllers;

use App\Core\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Display notifications page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $this->notificationService->getAll($user, 100);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'icon' => $n->icon,
                'link' => $n->link,
                'data' => $n->data,
                'is_read' => $n->isRead(),
                'time_ago' => $n->timeAgo(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get recent notifications for dropdown (AJAX).
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        $notifications = $this->notificationService->getAll($user, 10);

        return response()->json([
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'icon' => $n->icon,
                'link' => $n->link,
                'data' => $n->data,
                'is_read' => $n->isRead(),
                'time_ago' => $n->timeAgo(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'unread_count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Get unread notifications count (for navbar badge).
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, int $id)
    {
        $success = $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read' : 'Notification not found'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    /**
     * Delete a notification.
     */
    public function delete(Request $request, int $id)
    {
        $success = $this->notificationService->delete($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification deleted' : 'Notification not found'
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function deleteRead(Request $request)
    {
        $user = $request->user();
        $count = $this->notificationService->deleteRead($user);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Deleted {$count} read notifications"
        ]);
    }
}

