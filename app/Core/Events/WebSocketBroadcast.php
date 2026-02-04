<?php

namespace App\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebSocketBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $channelName;
    public string $eventName;
    public array $payload;

    /**
     * Create a new event instance.
     */
    public function __construct(string $channel, string $event, array $data = [])
    {
        $this->channelName = $channel;
        $this->eventName = $event;
        $this->payload = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Determine channel type based on prefix
        if (str_starts_with($this->channelName, 'user.') || 
            str_starts_with($this->channelName, 'combat.') ||
            $this->channelName === 'admin') {
            return [new PrivateChannel($this->channelName)];
        }

        if (str_starts_with($this->channelName, 'gang.')) {
            return [new PresenceChannel($this->channelName)];
        }

        return [new Channel($this->channelName)];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
