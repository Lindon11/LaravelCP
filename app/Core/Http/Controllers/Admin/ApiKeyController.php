<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\ApiKey;
use App\Core\Models\ApiRequestLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    /**
     * List all API keys
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApiKey::with('creator:id,username')
            ->withCount('requestLogs');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $apiKeys = $query->orderBy('created_at', 'desc')->paginate(20);

        // Add computed fields
        $apiKeys->getCollection()->transform(function ($key) {
            $key->requests_today = $key->requestLogs()
                ->whereDate('created_at', today())
                ->count();
            $key->is_expired = $key->expires_at && $key->expires_at->isPast();
            return $key;
        });

        return response()->json($apiKeys);
    }

    /**
     * Get API key details
     */
    public function show(int $id): JsonResponse
    {
        $apiKey = ApiKey::with('creator:id,username')
            ->withCount('requestLogs')
            ->findOrFail($id);

        // Get usage stats
        $apiKey->stats = $this->getKeyStats($apiKey);

        return response()->json($apiKey);
    }

    /**
     * Create new API key
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string|max:255',
            'rate_limit' => 'nullable|integer|min:1|max:10000',
            'daily_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $key = ApiKey::generateKey();
        $secret = ApiKey::generateSecret();

        $apiKey = ApiKey::create([
            'name' => $validated['name'],
            'key' => $key,
            'secret' => $secret,
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? null,
            'allowed_ips' => $validated['allowed_ips'] ?? null,
            'allowed_domains' => $validated['allowed_domains'] ?? null,
            'rate_limit' => $validated['rate_limit'] ?? 60,
            'daily_limit' => $validated['daily_limit'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Return with secret visible (only time it's shown)
        return response()->json([
            'message' => 'API key created successfully',
            'api_key' => $apiKey,
            'key' => $key,
            'secret' => $secret,
        ], 201);
    }

    /**
     * Update API key
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'string|max:255',
            'rate_limit' => 'nullable|integer|min:1|max:10000',
            'daily_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'sometimes|boolean',
        ]);

        $apiKey->update($validated);

        // Clear any cached rate limit data
        Cache::forget("api_rate_limit:{$apiKey->key}");

        return response()->json([
            'message' => 'API key updated successfully',
            'api_key' => $apiKey->fresh(),
        ]);
    }

    /**
     * Delete API key
     */
    public function destroy(int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        // Clear cache
        Cache::forget("api_rate_limit:{$apiKey->key}");

        $apiKey->delete();

        return response()->json([
            'message' => 'API key deleted successfully',
        ]);
    }

    /**
     * Toggle API key status
     */
    public function toggle(int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        return response()->json([
            'message' => $apiKey->is_active ? 'API key enabled' : 'API key disabled',
            'is_active' => $apiKey->is_active,
        ]);
    }

    /**
     * Regenerate API key secret
     */
    public function regenerateSecret(int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        $newSecret = ApiKey::generateSecret();
        $apiKey->update(['secret' => $newSecret]);

        return response()->json([
            'message' => 'API secret regenerated successfully',
            'secret' => $newSecret,
        ]);
    }

    /**
     * Get request logs for an API key
     */
    public function logs(Request $request, int $id): JsonResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        $query = $apiKey->requestLogs()
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'success') {
                $query->where('status_code', '>=', 200)->where('status_code', '<', 300);
            } elseif ($request->status === 'error') {
                $query->where('status_code', '>=', 400);
            }
        }

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->from));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Get API usage analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        // Overall stats
        $totalKeys = ApiKey::count();
        $activeKeys = ApiKey::where('is_active', true)->count();
        $totalRequests = ApiRequestLog::where('created_at', '>=', $startDate)->count();
        $errorRequests = ApiRequestLog::where('created_at', '>=', $startDate)
            ->where('status_code', '>=', 400)
            ->count();

        // Requests by day
        $requestsByDay = ApiRequestLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Top endpoints
        $topEndpoints = ApiRequestLog::select(
                'endpoint',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(response_time) as avg_response_time')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Requests by API key
        $requestsByKey = ApiKey::select('api_keys.id', 'api_keys.name', 'api_keys.key')
            ->withCount(['requestLogs as requests_count' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->orderByDesc('requests_count')
            ->limit(10)
            ->get();

        // Status code distribution
        $statusDistribution = ApiRequestLog::select(
                DB::raw('FLOOR(status_code / 100) * 100 as status_group'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('status_group')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    200 => '2xx Success',
                    300 => '3xx Redirect',
                    400 => '4xx Client Error',
                    500 => '5xx Server Error',
                ];
                return [$labels[$item->status_group] ?? "{$item->status_group}xx" => $item->total];
            });

        return response()->json([
            'overview' => [
                'total_keys' => $totalKeys,
                'active_keys' => $activeKeys,
                'total_requests' => $totalRequests,
                'error_requests' => $errorRequests,
                'error_rate' => $totalRequests > 0 ? round(($errorRequests / $totalRequests) * 100, 2) : 0,
            ],
            'requests_by_day' => $requestsByDay,
            'top_endpoints' => $topEndpoints,
            'requests_by_key' => $requestsByKey,
            'status_distribution' => $statusDistribution,
        ]);
    }

    /**
     * Get available permissions
     */
    public function permissions(): JsonResponse
    {
        $permissions = [
            ['key' => '*', 'label' => 'Full Access', 'description' => 'Access to all API endpoints'],
            ['key' => 'users:read', 'label' => 'Read Users', 'description' => 'View user information'],
            ['key' => 'users:write', 'label' => 'Write Users', 'description' => 'Create and modify users'],
            ['key' => 'game:read', 'label' => 'Read Game Data', 'description' => 'View game statistics and data'],
            ['key' => 'game:write', 'label' => 'Write Game Data', 'description' => 'Modify game data'],
            ['key' => 'economy:read', 'label' => 'Read Economy', 'description' => 'View economy statistics'],
            ['key' => 'economy:write', 'label' => 'Write Economy', 'description' => 'Modify economy (dangerous)'],
            ['key' => 'webhooks:trigger', 'label' => 'Trigger Webhooks', 'description' => 'Send webhook notifications'],
            ['key' => 'admin:read', 'label' => 'Read Admin', 'description' => 'View admin data and settings'],
        ];

        return response()->json($permissions);
    }

    /**
     * Get stats for a specific key
     */
    protected function getKeyStats(ApiKey $apiKey): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'requests_today' => $apiKey->requestLogs()->whereDate('created_at', $today)->count(),
            'requests_week' => $apiKey->requestLogs()->where('created_at', '>=', $thisWeek)->count(),
            'requests_month' => $apiKey->requestLogs()->where('created_at', '>=', $thisMonth)->count(),
            'avg_response_time' => round($apiKey->requestLogs()
                ->where('created_at', '>=', $thisMonth)
                ->avg('response_time') ?? 0),
            'error_rate' => $this->calculateErrorRate($apiKey, $thisMonth),
            'last_endpoints' => $apiKey->requestLogs()
                ->select('endpoint', 'method', 'status_code', 'created_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Calculate error rate for an API key
     */
    protected function calculateErrorRate(ApiKey $apiKey, Carbon $since): float
    {
        $total = $apiKey->requestLogs()->where('created_at', '>=', $since)->count();
        if ($total === 0) return 0;

        $errors = $apiKey->requestLogs()
            ->where('created_at', '>=', $since)
            ->where('status_code', '>=', 400)
            ->count();

        return round(($errors / $total) * 100, 2);
    }
}
