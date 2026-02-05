<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendErrorController extends Controller
{
    /**
     * Valid sources for error logging
     */
    protected const VALID_SOURCES = ['openpbbg', 'admin', 'backend', 'laravel-log'];

    /**
     * Determine error source from request
     */
    protected function determineSource(Request $request): string
    {
        // Check explicit source parameter
        $source = $request->input('app_source') ?? $request->input('source_app');

        if ($source && in_array($source, self::VALID_SOURCES)) {
            return $source;
        }

        // Try to determine from referer
        $referer = $request->header('Referer', '');

        if (str_contains($referer, '/admin')) {
            return 'admin';
        }

        // Default to openpbbg for frontend errors
        return 'openpbbg';
    }

    /**
     * Log frontend (JavaScript) errors from any frontend application
     */
    public function log(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'source' => 'nullable|string|max:500', // file source
            'app_source' => 'nullable|string|in:openpbbg,admin,backend,laravel-log', // application source
            'source_app' => 'nullable|string|in:openpbbg,admin,backend,laravel-log', // alias
            'line' => 'nullable|integer',
            'column' => 'nullable|integer',
            'stack' => 'nullable|string|max:5000',
            'url' => 'nullable|string|max:500',
            'user_agent' => 'nullable|string|max:500',
            'component' => 'nullable|string|max:255',
            'severity' => 'nullable|string|in:error,warning,info,critical,emergency',
        ]);

        $appSource = $this->determineSource($request);
        $errorType = $appSource === 'admin' ? 'AdminFrontendError' : 'FrontendError';

        // Check if similar error exists (group duplicates)
        $existing = ErrorLog::where('type', $errorType)
            ->where('message', $validated['message'])
            ->where('file', $validated['source'] ?? 'unknown')
            ->where('line', $validated['line'] ?? 0)
            ->first();

        if ($existing) {
            // Update existing error
            $existing->increment('count');
            $existing->update(['last_seen_at' => now()]);
        } else {
            // Create new error log
            ErrorLog::create([
                'type' => $errorType,
                'message' => $validated['message'],
                'file' => $validated['source'] ?? 'unknown',
                'line' => $validated['line'] ?? 0,
                'trace' => $validated['stack'] ?? null,
                'url' => $validated['url'] ?? $request->header('Referer'),
                'method' => 'GET',
                'ip' => $request->ip(),
                'user_id' => Auth::id(),
                'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
                'context' => [
                    'component' => $validated['component'] ?? null,
                    'column' => $validated['column'] ?? null,
                    'severity' => $validated['severity'] ?? 'error',
                    'frontend' => true,
                    'app_source' => $appSource,
                ],
                'last_seen_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Error logged successfully',
        ]);
    }

    /**
     * Log API call failures from frontend
     */
    public function logApiError(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:500',
            'method' => 'required|string|in:GET,POST,PUT,PATCH,DELETE',
            'status_code' => 'required|integer',
            'error_message' => 'required|string|max:1000',
            'request_data' => 'nullable|array',
            'response_data' => 'nullable|array',
            'app_source' => 'nullable|string|in:openpbbg,admin,backend,laravel-log',
            'source_app' => 'nullable|string|in:openpbbg,admin,backend,laravel-log',
        ]);

        $appSource = $this->determineSource($request);
        $errorType = $appSource === 'admin' ? 'AdminApiError' : 'FrontendApiError';

        ErrorLog::create([
            'type' => $errorType,
            'message' => "API Error: {$validated['method']} {$validated['endpoint']} - {$validated['error_message']}",
            'file' => $validated['endpoint'],
            'line' => $validated['status_code'],
            'url' => $request->header('Referer'),
            'method' => $validated['method'],
            'ip' => $request->ip(),
            'user_id' => Auth::id(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'endpoint' => $validated['endpoint'],
                'status_code' => $validated['status_code'],
                'request_data' => $validated['request_data'] ?? null,
                'response_data' => $validated['response_data'] ?? null,
                'frontend' => true,
                'app_source' => $appSource,
            ],
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API error logged successfully',
        ]);
    }

    /**
     * Log Vue component errors
     */
    public function logVueError(Request $request)
    {
        $validated = $request->validate([
            'error' => 'required|string|max:1000',
            'component' => 'required|string|max:255',
            'hook' => 'nullable|string|max:100',
            'info' => 'nullable|string|max:500',
            'props' => 'nullable|array',
            'app_source' => 'nullable|string|in:openpbbg,admin,backend,laravel-log',
            'source_app' => 'nullable|string|in:openpbbg,admin,backend,laravel-log',
        ]);

        $appSource = $this->determineSource($request);
        $errorType = $appSource === 'admin' ? 'AdminVueError' : 'VueComponentError';

        ErrorLog::create([
            'type' => $errorType,
            'message' => "[{$validated['component']}] {$validated['error']}",
            'file' => $validated['component'],
            'line' => 0,
            'url' => $request->header('Referer'),
            'method' => 'GET',
            'ip' => $request->ip(),
            'user_id' => Auth::id(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'component' => $validated['component'],
                'hook' => $validated['hook'] ?? null,
                'info' => $validated['info'] ?? null,
                'props' => $validated['props'] ?? null,
                'frontend' => true,
                'app_source' => $appSource,
            ],
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vue error logged successfully',
        ]);
    }
}
