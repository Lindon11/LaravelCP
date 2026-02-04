<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    /**
     * Send a message to Discord webhook
     */
    public function send(string $webhookUrl, array $data): bool
    {
        try {
            $response = Http::post($webhookUrl, $data);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Discord webhook failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a simple text message
     */
    public function sendMessage(string $webhookUrl, string $message): bool
    {
        return $this->send($webhookUrl, [
            'content' => $message,
        ]);
    }

    /**
     * Send an embed message
     */
    public function sendEmbed(string $webhookUrl, array $embed): bool
    {
        return $this->send($webhookUrl, [
            'embeds' => [$embed],
        ]);
    }

    /**
     * Create a game event embed
     */
    public function createGameEventEmbed(string $title, string $description, string $color = '3447003'): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'color' => hexdec($color),
            'timestamp' => now()->toIso8601String(),
            'footer' => [
                'text' => config('app.name'),
            ],
        ];
    }

    /**
     * Send player notification
     */
    public function sendPlayerNotification(string $webhookUrl, string $username, string $event, string $details): bool
    {
        return $this->sendEmbed($webhookUrl, [
            'title' => "🎮 {$event}",
            'description' => $details,
            'color' => 3066993, // Green
            'fields' => [
                ['name' => 'Player', 'value' => $username, 'inline' => true],
                ['name' => 'Time', 'value' => now()->format('Y-m-d H:i:s'), 'inline' => true],
            ],
            'footer' => [
                'text' => config('app.name'),
            ],
        ]);
    }

    /**
     * Send admin alert
     */
    public function sendAdminAlert(string $webhookUrl, string $title, string $message, string $severity = 'info'): bool
    {
        $colors = [
            'info' => 3447003,    // Blue
            'warning' => 16776960, // Yellow
            'error' => 15158332,   // Red
            'success' => 3066993,  // Green
        ];

        return $this->sendEmbed($webhookUrl, [
            'title' => "⚠️ {$title}",
            'description' => $message,
            'color' => $colors[$severity] ?? $colors['info'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
