<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackWebhookService
{
    /**
     * Send a message to Slack webhook
     */
    public function send(string $webhookUrl, array $data): bool
    {
        try {
            $response = Http::post($webhookUrl, $data);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Slack webhook failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a simple text message
     */
    public function sendMessage(string $webhookUrl, string $message, ?string $channel = null): bool
    {
        $payload = ['text' => $message];

        if ($channel) {
            $payload['channel'] = $channel;
        }

        return $this->send($webhookUrl, $payload);
    }

    /**
     * Send a message with blocks
     */
    public function sendBlocks(string $webhookUrl, array $blocks, ?string $text = null): bool
    {
        return $this->send($webhookUrl, [
            'text' => $text ?? 'New notification',
            'blocks' => $blocks,
        ]);
    }

    /**
     * Create a section block
     */
    public function sectionBlock(string $text, ?array $fields = null): array
    {
        $block = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $text,
            ],
        ];

        if ($fields) {
            $block['fields'] = array_map(fn($f) => [
                'type' => 'mrkdwn',
                'text' => $f,
            ], $fields);
        }

        return $block;
    }

    /**
     * Create a divider block
     */
    public function dividerBlock(): array
    {
        return ['type' => 'divider'];
    }

    /**
     * Create a header block
     */
    public function headerBlock(string $text): array
    {
        return [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => $text,
            ],
        ];
    }

    /**
     * Send game event notification
     */
    public function sendGameEvent(string $webhookUrl, string $event, string $player, string $details): bool
    {
        $blocks = [
            $this->headerBlock("🎮 {$event}"),
            $this->sectionBlock($details, [
                "*Player:*\n{$player}",
                "*Time:*\n" . now()->format('Y-m-d H:i:s'),
            ]),
        ];

        return $this->sendBlocks($webhookUrl, $blocks, "{$event}: {$player}");
    }

    /**
     * Send admin alert
     */
    public function sendAdminAlert(string $webhookUrl, string $title, string $message, string $severity = 'info'): bool
    {
        $emojis = [
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'error' => '🚨',
            'success' => '✅',
        ];

        $emoji = $emojis[$severity] ?? $emojis['info'];

        $blocks = [
            $this->headerBlock("{$emoji} {$title}"),
            $this->sectionBlock($message),
            $this->dividerBlock(),
            $this->sectionBlock("_" . config('app.name') . " • " . now()->format('Y-m-d H:i:s') . "_"),
        ];

        return $this->sendBlocks($webhookUrl, $blocks, "{$title}: {$message}");
    }
}
