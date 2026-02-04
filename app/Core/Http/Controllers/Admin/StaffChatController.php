<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\StaffChatMessage;
use App\Core\Models\StaffChatReadStatus;
use App\Core\Models\User;
use App\Core\Facades\TextFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class StaffChatController extends Controller
{
    /**
     * Get staff chat messages.
     */
    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get last 100 messages
        $messages = StaffChatMessage::with(['user:id,username,name'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'username' => $msg->user->username ?? $msg->user->name,
                'content' => TextFormatter::format($msg->content),
                'content_raw' => $msg->content,
                'mentioned_user_id' => $msg->mentioned_user_id,
                'created_at' => $msg->created_at->toIso8601String(),
            ]);

        // Get online staff (admins/moderators active in last 5 minutes)
        $onlineStaff = User::role(['admin', 'moderator'])
            ->where('last_active', '>=', now()->subMinutes(5))
            ->select('id', 'username', 'name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'username' => $u->username ?? $u->name,
            ]);

        // Update user's read status
        $this->updateReadStatus($user->id, $messages->last()['id'] ?? null);

        // Update user's last active time for online status
        $user->update(['last_active' => now()]);

        return response()->json([
            'messages' => $messages,
            'online_staff' => $onlineStaff,
            'current_user_id' => $user->id,
        ]);
    }

    /**
     * Send a new message.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $content = $request->input('content');

        // Check for @mentions
        $mentionedUserId = null;
        if (preg_match('/@(\w+)/', $content, $matches)) {
            $mentionedUser = User::where('username', $matches[1])
                ->orWhere('name', $matches[1])
                ->first();
            if ($mentionedUser) {
                $mentionedUserId = $mentionedUser->id;
            }
        }

        $message = StaffChatMessage::create([
            'user_id' => $user->id,
            'content' => $content,
            'mentioned_user_id' => $mentionedUserId,
        ]);

        // Update user's last active
        $user->update(['last_active' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'username' => $user->username ?? $user->name,
                'content' => $message->content,
                'mentioned_user_id' => $message->mentioned_user_id,
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get unread message count.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();

        $readStatus = StaffChatReadStatus::where('user_id', $user->id)->first();
        $lastReadId = $readStatus?->last_read_message_id ?? 0;

        $unreadCount = StaffChatMessage::where('id', '>', $lastReadId)
            ->where('user_id', '!=', $user->id) // Don't count own messages
            ->count();

        return response()->json([
            'count' => $unreadCount,
        ]);
    }

    /**
     * Update user's read status.
     */
    private function updateReadStatus(int $userId, ?int $lastMessageId): void
    {
        if (!$lastMessageId) {
            return;
        }

        StaffChatReadStatus::updateOrCreate(
            ['user_id' => $userId],
            [
                'last_read_message_id' => $lastMessageId,
                'last_read_at' => now(),
            ]
        );
    }
}
