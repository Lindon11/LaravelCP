<?php

namespace App\Plugins\Chat\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Chat\ChatModule;
use App\Plugins\Chat\Models\ChatChannel;
use App\Plugins\Chat\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected ChatModule $module;
    
    public function __construct()
    {
        $this->module = new ChatModule();
    }
    
    /**
     * Get all accessible channels
     */
    public function channels(Request $request)
    {
        $channels = $this->module->getChannels($request->user());
        return response()->json($channels);
    }
    
    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->module->getUnreadCount($request->user());
        return response()->json(['unread_count' => $count]);
    }
    
    /**
     * Get messages for a channel
     */
    public function messages(Request $request, ChatChannel $channel)
    {
        try {
            $messages = $this->module->getMessages(
                $request->user(),
                $channel,
                $request->get('per_page', 50)
            );
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
    
    /**
     * Send a message
     */
    public function sendMessage(Request $request, ChatChannel $channel)
    {
        $request->validate([
            'message' => 'required|string|max:65535',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);
        
        try {
            $message = $this->module->sendMessage(
                $request->user(),
                $channel,
                $request->message,
                $request->reply_to_id
            );
            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, ChatMessage $message)
    {
        try {
            $this->module->deleteMessage($request->user(), $message);
            return response()->json(['message' => 'Message deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
    
    /**
     * Add reaction
     */
    public function addReaction(Request $request, ChatMessage $message)
    {
        $request->validate(['emoji' => 'required|string']);
        
        try {
            $reaction = $this->module->addReaction(
                $request->user(),
                $message,
                $request->emoji
            );
            return response()->json($reaction);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Remove reaction
     */
    public function removeReaction(Request $request, ChatMessage $message)
    {
        $request->validate(['emoji' => 'required|string']);
        
        $this->module->removeReaction(
            $request->user(),
            $message,
            $request->emoji
        );
        return response()->json(['message' => 'Reaction removed']);
    }
    
    /**
     * Create channel
     */
    public function createChannel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:public,private,gang',
            'description' => 'nullable|string|max:500',
        ]);
        
        try {
            $channel = $this->module->createChannel(
                $request->user(),
                $request->name,
                $request->type,
                $request->description
            );
            return response()->json($channel, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
