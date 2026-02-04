<?php

namespace App\Plugins\Chat\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Chat\Models\DirectMessage;
use App\Core\Models\User;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    /**
     * Get all DM conversations for the user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get unique conversations
        $conversations = DirectMessage::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->get()
            ->unique(function ($item) {
                return $item->from_user_id === $user->id 
                    ? $item->to_user_id 
                    : $item->from_user_id;
            });

        return response()->json($conversations->values());
    }

    /**
     * Get conversation between two users
     */
    public function show(Request $request, User $user)
    {
        $authUser = $request->user();

        $messages = DirectMessage::betweenUsers($authUser->id, $user->id)
            ->latest()
            ->paginate(50);

        // Mark as read
        DirectMessage::where('to_user_id', $authUser->id)
            ->where('from_user_id', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json($messages);
    }

    /**
     * Send a direct message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id|different:user_id',
            'message' => 'required|string|max:65535',
        ]);

        // Sanitize message to prevent XSS
        $sanitizedMessage = strip_tags($validated['message']);

        $message = DirectMessage::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $validated['to_user_id'],
            'message' => $sanitizedMessage,
        ]);

        return response()->json($message->load(['sender', 'recipient']), 201);
    }

    /**
     * Delete a direct message
     */
    public function destroy(Request $request, DirectMessage $message)
    {
        // Only sender can delete
        if ($message->from_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }

    /**
     * Get unread message count
     */
    public function unreadCount(Request $request)
    {
        $unreadCount = DirectMessage::where('to_user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Mark all messages from a user as read
     */
    public function markAsRead(Request $request, User $user)
    {
        DirectMessage::where('to_user_id', $request->user()->id)
            ->where('from_user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'Messages marked as read']);
    }
}
