<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\Request;

class FrontendErrorController extends Controller
{
    /**
     * Log frontend (JavaScript) errors from OpenPBBG
     */
    public function log(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'source' => 'nullable|string|max:500',
            'line' => 'nullable|integer',
            'column' => 'nullable|integer',
            'stack' => 'nullable|string|max:5000',
            'url' => 'nullable|string|max:500',
            'user_agent' => 'nullable|string|max:500',
            'component' => 'nullable|string|max:255',
            'severity' => 'nullable|string|in:error,warning,info',
        ]);

        // Check if similar error exists (group duplicates)
        $existing = ErrorLog::where('type', 'FrontendError')
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
                'type' => 'FrontendError',
                'message' => $validated['message'],
                'file' => $validated['source'] ?? 'unknown',
                'line' => $validated['line'] ?? 0,
                'trace' => $validated['stack'] ?? null,
                'url' => $validated['url'] ?? $request->header('Referer'),
                'method' => 'GET',
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
                'context' => [
                    'component' => $validated['component'] ?? null,
                    'column' => $validated['column'] ?? null,
                    'severity' => $validated['severity'] ?? 'error',
                    'frontend' => true,
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
        ]);

        ErrorLog::create([
            'type' => 'FrontendApiError',
            'message' => "API Error: {$validated['method']} {$validated['endpoint']} - {$validated['error_message']}",
            'file' => $validated['endpoint'],
            'line' => $validated['status_code'],
            'url' => $request->header('Referer'),
            'method' => $validated['method'],
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'endpoint' => $validated['endpoint'],
                'status_code' => $validated['status_code'],
                'request_data' => $validated['request_data'] ?? null,
                'response_data' => $validated['response_data'] ?? null,
                'frontend' => true,
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
        ]);

        ErrorLog::create([
            'type' => 'VueComponentError',
            'message' => "[{$validated['component']}] {$validated['error']}",
            'file' => $validated['component'],
            'line' => 0,
            'url' => $request->header('Referer'),
            'method' => 'GET',
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'component' => $validated['component'],
                'hook' => $validated['hook'] ?? null,
                'info' => $validated['info'] ?? null,
                'props' => $validated['props'] ?? null,
                'frontend' => true,
            ],
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vue error logged successfully',
        ]);
    }
}
