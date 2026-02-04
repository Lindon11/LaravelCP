<?php

namespace App\Plugins\Messaging\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Messaging\Models\Message;
use App\Core\Models\User;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    /**
     * Get inbox
     */
    public function inbox(Request $request)
    {
        $messages = Message::inbox(auth()->id())
            ->with('sender:id,username,avatar,level')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'unread_count' => Message::inbox(auth()->id())->unread()->count(),
        ]);
    }

    /**
     * Get sent messages
     */
    public function sent(Request $request)
    {
        $messages = Message::sent(auth()->id())
            ->with('recipient:id,username,avatar,level')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Get conversation with a user
     */
    public function conversation(int $userId)
    {
        $currentUserId = auth()->id();

        $messages = Message::where(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $currentUserId)
                ->where('recipient_id', $userId)
                ->where('sender_deleted', false);
        })->orWhere(function ($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $userId)
                ->where('recipient_id', $currentUserId)
                ->where('recipient_deleted', false);
        })
            ->with(['sender:id,username,avatar', 'recipient:id,username,avatar'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('recipient_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $otherUser = User::select('id', 'username', 'avatar', 'level')->find($userId);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'user' => $otherUser,
        ]);
    }

    /**
     * Get single message
     */
    public function show(int $id)
    {
        $message = Message::with(['sender:id,username,avatar', 'recipient:id,username,avatar'])
            ->find($id);

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        $userId = auth()->id();
        if ($message->sender_id !== $userId && $message->recipient_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Mark as read if recipient
        if ($message->recipient_id === $userId && !$message->read_at) {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Send a message
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:100',
            'body' => 'required|string|max:5000',
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        $senderId = auth()->id();

        if ($validated['recipient_id'] == $senderId) {
            return response()->json(['success' => false, 'message' => 'Cannot message yourself'], 400);
        }

        // Check if blocked
        $recipient = User::find($validated['recipient_id']);
        if (!$recipient) {
            return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
        }

        $message = Message::create([
            'sender_id' => $senderId,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'reply_to_id' => $validated['reply_to_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent',
            'data' => $message->load('recipient:id,username'),
        ]);
    }

    /**
     * Delete a message
     */
    public function delete(int $id)
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        $userId = auth()->id();

        if ($message->sender_id === $userId) {
            $message->update(['sender_deleted' => true]);
        } elseif ($message->recipient_id === $userId) {
            $message->update(['recipient_deleted' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // If both deleted, actually delete
        if ($message->sender_deleted && $message->recipient_deleted) {
            $message->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
    }

    /**
     * Mark message as read
     */
    public function markRead(int $id)
    {
        $message = Message::find($id);

        if (!$message || $message->recipient_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Marked as read',
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllRead()
    {
        Message::inbox(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All messages marked as read',
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        return response()->json([
            'success' => true,
            'count' => Message::inbox(auth()->id())->unread()->count(),
        ]);
    }
}
