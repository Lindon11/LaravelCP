<?php

namespace App\Core\Http\Controllers;

use App\Core\Services\WebSocketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebSocketController extends Controller
{
    public function __construct(
        protected WebSocketService $wsService
    ) {}

    /**
     * Authorize a private/presence channel subscription
     */
    public function authorize(Request $request): JsonResponse
    {
        $user = $request->user();
        $channel = $request->input('channel_name');

        if (!$this->wsService->authorizeChannel($user, $channel)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // For presence channels, include user info
        if (str_starts_with($channel, 'presence-')) {
            return response()->json([
                'user_id' => $user->id,
                'user_info' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar ?? null,
                ],
            ]);
        }

        return response()->json(['auth' => true]);
    }

    /**
     * Long-polling fallback for clients without WebSocket support
     */
    public function poll(Request $request): JsonResponse
    {
        $request->validate([
            'channels' => 'required|array',
            'channels.*' => 'string',
            'since' => 'nullable|string',
        ]);

        $user = $request->user();
        $messages = [];

        foreach ($request->channels as $channel) {
            // Check authorization
            if (!$this->wsService->authorizeChannel($user, $channel)) {
                continue;
            }

            $channelMessages = $this->wsService->getMessages($channel, $request->since);
            $messages = array_merge($messages, $channelMessages);
        }

        // Sort by timestamp
        usort($messages, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        return response()->json([
            'messages' => $messages,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get online users count
     */
    public function onlineCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->wsService->getOnlineCount(),
        ]);
    }

    /**
     * Heartbeat to maintain online status
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->wsService->setOnline($user);

        return response()->json([
            'status' => 'ok',
            'online_count' => $this->wsService->getOnlineCount(),
        ]);
    }

    /**
     * Get presence channel members
     */
    public function presenceMembers(Request $request, string $channel): JsonResponse
    {
        $user = $request->user();

        if (!$this->wsService->authorizeChannel($user, $channel)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'members' => array_values($this->wsService->getPresenceMembers($channel)),
        ]);
    }
}
