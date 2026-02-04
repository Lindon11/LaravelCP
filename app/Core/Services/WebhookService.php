<?php

namespace App\Core\Services;

use App\Core\Models\Webhook;
use App\Core\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Available webhook events
     */
    public const EVENTS = [
        // User events
        'user.registered',
        'user.login',
        'user.logout',
        'user.level_up',
        'user.rank_up',
        'user.banned',
        'user.unbanned',

        // Combat events
        'combat.attack',
        'combat.kill',
        'combat.death',

        // Economy events
        'economy.transaction',
        'economy.transfer',
        'economy.purchase',

        // Gang events
        'gang.created',
        'gang.member_joined',
        'gang.member_left',
        'gang.war_started',

        // Game events
        'crime.completed',
        'mission.completed',
        'achievement.unlocked',
        'item.acquired',

        // Admin events
        'admin.announcement',
        'admin.maintenance',
        'ticket.created',
        'ticket.replied',
    ];

    /**
     * Dispatch a webhook event
     */
    public function dispatch(string $event, array $payload = []): void
    {
        $webhooks = Webhook::where('is_active', true)
            ->get()
            ->filter(fn($webhook) => $webhook->subscribesTo($event));

        foreach ($webhooks as $webhook) {
            dispatch(function () use ($webhook, $event, $payload) {
                $this->deliver($webhook, $event, $payload);
            })->afterResponse();
        }
    }

    /**
     * Deliver webhook to endpoint
     */
    public function deliver(Webhook $webhook, string $event, array $payload, int $attempt = 1): WebhookDelivery
    {
        $fullPayload = [
            'id' => (string) Str::uuid(),
            'event' => $event,
            'created_at' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $signature = $this->generateSignature($fullPayload, $webhook->secret);

        $headers = array_merge($webhook->headers ?? [], [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $event,
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => (string) time(),
            'User-Agent' => 'LaravelCP-Webhook/1.0',
        ]);

        $startTime = microtime(true);
        $delivery = new WebhookDelivery([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $fullPayload,
            'attempt' => $attempt,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->retry($webhook->retry_count, 1000)
                ->post($webhook->url, $fullPayload);

            $delivery->fill([
                'response_code' => $response->status(),
                'response_body' => Str::limit($response->body(), 5000),
                'response_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'delivered_at' => now(),
            ]);

            if ($response->successful()) {
                $webhook->resetFailure();
            } else {
                $webhook->incrementFailure();
            }

            $webhook->update([
                'last_triggered_at' => now(),
                'last_response_code' => $response->status(),
            ]);

        } catch (\Exception $e) {
            $delivery->fill([
                'response_code' => 0,
                'error' => $e->getMessage(),
                'response_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
            ]);

            $webhook->incrementFailure();

            Log::warning('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }

        $delivery->save();

        return $delivery;
    }

    /**
     * Generate HMAC signature for payload
     */
    public function generateSignature(array $payload, ?string $secret): string
    {
        if (!$secret) {
            return '';
        }

        return 'sha256=' . hash_hmac('sha256', json_encode($payload), $secret);
    }

    /**
     * Verify incoming webhook signature
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Get all available events
     */
    public function getAvailableEvents(): array
    {
        return self::EVENTS;
    }

    /**
     * Retry a failed delivery
     */
    public function retry(WebhookDelivery $delivery): WebhookDelivery
    {
        return $this->deliver(
            $delivery->webhook,
            $delivery->event,
            $delivery->payload['data'] ?? [],
            $delivery->attempt + 1
        );
    }

    /**
     * Create a new webhook
     */
    public function create(array $data): Webhook
    {
        return Webhook::create([
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'url' => $data['url'],
            'secret' => $data['secret'] ?? Str::random(32),
            'events' => $data['events'] ?? ['*'],
            'is_active' => $data['is_active'] ?? true,
            'headers' => $data['headers'] ?? [],
            'retry_count' => $data['retry_count'] ?? 3,
        ]);
    }

    /**
     * Test a webhook endpoint
     */
    public function test(Webhook $webhook): WebhookDelivery
    {
        return $this->deliver($webhook, 'webhook.test', [
            'message' => 'This is a test webhook delivery',
            'webhook_id' => $webhook->id,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
