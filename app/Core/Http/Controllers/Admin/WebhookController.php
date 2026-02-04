<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Webhook;
use App\Core\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    /**
     * List all webhooks
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = Webhook::with(['deliveries' => fn($q) => $q->latest()->limit(5)])
            ->latest()
            ->paginate(20);

        return response()->json($webhooks);
    }

    /**
     * Get available webhook events
     */
    public function events(): JsonResponse
    {
        return response()->json([
            'events' => $this->webhookService->getAvailableEvents(),
        ]);
    }

    /**
     * Create a new webhook
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'headers' => 'nullable|array',
            'is_active' => 'boolean',
            'retry_count' => 'integer|min:0|max:10',
        ]);

        $webhook = $this->webhookService->create($validated);

        return response()->json([
            'message' => 'Webhook created successfully.',
            'webhook' => $webhook,
        ], 201);
    }

    /**
     * Show a specific webhook
     */
    public function show(int $id): JsonResponse
    {
        $webhook = Webhook::with(['deliveries' => fn($q) => $q->latest()->limit(20)])
            ->findOrFail($id);

        return response()->json($webhook);
    }

    /**
     * Update a webhook
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'url' => 'url',
            'events' => 'array|min:1',
            'events.*' => 'string',
            'headers' => 'nullable|array',
            'is_active' => 'boolean',
            'retry_count' => 'integer|min:0|max:10',
        ]);

        $webhook->update($validated);

        return response()->json([
            'message' => 'Webhook updated successfully.',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Delete a webhook
     */
    public function destroy(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->deliveries()->delete();
        $webhook->delete();

        return response()->json([
            'message' => 'Webhook deleted successfully.',
        ]);
    }

    /**
     * Toggle webhook active status
     */
    public function toggle(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->update(['is_active' => !$webhook->is_active]);

        return response()->json([
            'message' => 'Webhook ' . ($webhook->is_active ? 'activated' : 'deactivated') . ' successfully.',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Test a webhook
     */
    public function test(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $delivery = $this->webhookService->test($webhook);

        return response()->json([
            'message' => $delivery->isSuccessful() ? 'Test delivery successful!' : 'Test delivery failed.',
            'delivery' => $delivery,
        ]);
    }

    /**
     * Get webhook deliveries
     */
    public function deliveries(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $deliveries = $webhook->deliveries()
            ->latest()
            ->paginate(50);

        return response()->json($deliveries);
    }

    /**
     * Retry a failed delivery
     */
    public function retryDelivery(int $id, int $deliveryId): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $delivery = $webhook->deliveries()->findOrFail($deliveryId);
        
        $newDelivery = $this->webhookService->retry($delivery);

        return response()->json([
            'message' => $newDelivery->isSuccessful() ? 'Retry successful!' : 'Retry failed.',
            'delivery' => $newDelivery,
        ]);
    }

    /**
     * Regenerate webhook secret
     */
    public function regenerateSecret(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $newSecret = \Illuminate\Support\Str::random(32);
        $webhook->update(['secret' => $newSecret]);

        return response()->json([
            'message' => 'Webhook secret regenerated.',
            'secret' => $newSecret,
        ]);
    }
}
