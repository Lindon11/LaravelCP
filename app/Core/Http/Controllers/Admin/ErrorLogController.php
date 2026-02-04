<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\ErrorLog;
use App\Core\Services\LaravelLogReader;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    /**
     * Display a listing of error logs
     */
    public function index(Request $request)
    {
        $query = ErrorLog::with('user:id,username')
            ->orderBy('last_seen_at', 'desc');

        // Filter by resolved status
        if ($request->has('resolved')) {
            $query->where('resolved', $request->boolean('resolved'));
        }

        // Filter by type/level
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by level (error, warning, etc.)
        if ($request->filled('level')) {
            $level = $request->level;
            // Map frontend levels to database types
            $query->where(function($q) use ($level) {
                $q->where('type', 'like', "%{$level}%")
                  ->orWhereJsonContains('context->severity', $level);
            });
        }

        // Filter by source (openpbbg, admin, backend, laravel-log)
        if ($request->filled('source')) {
            $source = $request->source;
            $query->where(function($q) use ($source) {
                // Check context->app_source
                $q->whereJsonContains('context->app_source', $source);

                // Also handle backend errors that don't have app_source set
                if ($source === 'backend') {
                    $q->orWhere(function($subQ) {
                        $subQ->whereNull('context->app_source')
                             ->whereNull('context->frontend');
                    });
                    $q->orWhereJsonContains('context->frontend', false);
                }
            });
        }

        // Date range filter
        if ($request->filled('dateRange')) {
            switch ($request->dateRange) {
                case 'today':
                    $query->whereDate('last_seen_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('last_seen_at', today()->subDay());
                    break;
                case 'week':
                    $query->where('last_seen_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('last_seen_at', '>=', now()->subMonth());
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        $errors = $query->paginate($perPage);

        // Transform errors to include level and source fields for frontend
        $errors->getCollection()->transform(function ($error) {
            $error->level = $this->determineLevel($error);
            $error->source = $this->determineSource($error);
            return $error;
        });

        // Get error types for filtering
        $errorTypes = ErrorLog::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // Get available sources
        $sources = [
            'backend' => 'Backend (PHP)',
            'admin' => 'Admin Panel',
            'openpbbg' => 'OpenPBBG Frontend',
            'laravel-log' => 'Laravel Log File',
        ];

        // Statistics by level (only unresolved errors)
        $stats = [
            'emergency' => ErrorLog::where('resolved', false)->where(function($q) {
                $q->where('type', 'like', '%Emergency%')->orWhereJsonContains('context->severity', 'emergency');
            })->count(),
            'critical' => ErrorLog::where('resolved', false)->where(function($q) {
                $q->where('type', 'like', '%Critical%')->orWhereJsonContains('context->severity', 'critical');
            })->count(),
            'error' => ErrorLog::where('resolved', false)->where(function($q) {
                $q->where('type', 'like', '%Error%')->orWhereJsonContains('context->severity', 'error');
            })->count(),
            'warning' => ErrorLog::where('resolved', false)->where(function($q) {
                $q->where('type', 'like', '%Warning%')->orWhereJsonContains('context->severity', 'warning');
            })->count(),
            'info' => ErrorLog::where('resolved', false)->where(function($q) {
                $q->where('type', 'like', '%Info%')->orWhereJsonContains('context->severity', 'info');
            })->count(),
            'total' => ErrorLog::where('resolved', false)->count(),
            'unresolved' => ErrorLog::where('resolved', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $errors->items(),
            'current_page' => $errors->currentPage(),
            'last_page' => $errors->lastPage(),
            'total' => $errors->total(),
            'error_types' => $errorTypes,
            'sources' => $sources,
            'stats' => $stats,
        ]);
    }

    /**
     * Determine the error level from type/context
     */
    protected function determineLevel(ErrorLog $error): string
    {
        $type = strtolower($error->type);
        $context = $error->context ?? [];

        // Check context severity first
        if (!empty($context['severity'])) {
            return $context['severity'];
        }

        // Check log_level from Laravel log files
        if (!empty($context['log_level'])) {
            return $context['log_level'];
        }

        // Determine from type
        if (str_contains($type, 'emergency')) return 'emergency';
        if (str_contains($type, 'critical')) return 'critical';
        if (str_contains($type, 'warning')) return 'warning';
        if (str_contains($type, 'info')) return 'info';
        if (str_contains($type, 'debug')) return 'debug';

        // Default based on error type
        if (str_contains($type, 'exception') || str_contains($type, 'error')) {
            return 'error';
        }

        return 'error';
    }

    /**
     * Determine the error source from context
     */
    protected function determineSource(ErrorLog $error): string
    {
        $context = $error->context ?? [];

        // Check explicit app_source
        if (!empty($context['app_source'])) {
            return $context['app_source'];
        }

        // Check frontend flag
        if (!empty($context['frontend'])) {
            // Try to determine from type
            $type = strtolower($error->type);
            if (str_contains($type, 'admin')) {
                return 'admin';
            }
            return 'openpbbg';
        }

        // Check if from log file
        if (!empty($context['from_log_file'])) {
            return 'laravel-log';
        }

        // Default to backend
        return 'backend';
    }

    /**
     * Display the specified error log
     */
    public function show(int $id)
    {
        $error = ErrorLog::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'error' => $error,
        ]);
    }

    /**
     * Mark error as resolved
     */
    public function resolve(int $id)
    {
        $error = ErrorLog::findOrFail($id);
        $error->update(['resolved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Error marked as resolved',
        ]);
    }

    /**
     * Mark error as unresolved
     */
    public function unresolve(int $id)
    {
        $error = ErrorLog::findOrFail($id);
        $error->update(['resolved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Error marked as unresolved',
        ]);
    }

    /**
     * Delete specific error log
     */
    public function destroy(int $id)
    {
        $error = ErrorLog::findOrFail($id);
        $error->delete();

        return response()->json([
            'success' => true,
            'message' => 'Error log deleted',
        ]);
    }

    /**
     * Bulk resolve errors
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:error_logs,id',
        ]);

        $count = ErrorLog::whereIn('id', $request->ids)
            ->update(['resolved' => true]);

        return response()->json([
            'success' => true,
            'message' => "Resolved {$count} error(s)",
            'count' => $count,
        ]);
    }

    /**
     * Bulk delete errors
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:error_logs,id',
        ]);

        $count = ErrorLog::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} error(s)",
            'count' => $count,
        ]);
    }

    /**
     * Delete all resolved errors
     */
    public function deleteResolved()
    {
        $count = ErrorLog::where('resolved', true)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} resolved error(s)",
            'count' => $count,
        ]);
    }

    /**
     * Delete old errors (older than specified days)
     */
    public function deleteOld(Request $request)
    {
        $days = $request->input('days', 30);

        $count = ErrorLog::where('last_seen_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} old error(s)",
            'count' => $count,
        ]);
    }

    /**
     * Clear all error logs
     */
    public function clearAll()
    {
        $count = ErrorLog::count();
        ErrorLog::truncate();

        return response()->json([
            'success' => true,
            'message' => "Cleared {$count} error log(s)",
            'count' => $count,
        ]);
    }

    /**
     * Get error statistics
     */
    public function statistics()
    {
        $stats = [
            'total_errors' => ErrorLog::count(),
            'unresolved_errors' => ErrorLog::where('resolved', false)->count(),
            'resolved_errors' => ErrorLog::where('resolved', true)->count(),
            'unique_error_types' => ErrorLog::distinct('type')->count(),
            'errors_last_hour' => ErrorLog::where('last_seen_at', '>=', now()->subHour())->count(),
            'errors_last_24h' => ErrorLog::where('last_seen_at', '>=', now()->subDay())->count(),
            'errors_last_week' => ErrorLog::where('last_seen_at', '>=', now()->subWeek())->count(),
            'most_common_errors' => ErrorLog::select('type', 'message')
                ->selectRaw('COUNT(*) as occurrences')
                ->selectRaw('SUM(count) as total_count')
                ->where('resolved', false)
                ->groupBy('type', 'message')
                ->orderByDesc('total_count')
                ->limit(10)
                ->get(),
            'errors_by_type' => ErrorLog::select('type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('type')
                ->orderByDesc('count')
                ->get(),
            'errors_by_source' => [
                'backend' => ErrorLog::where(function($q) {
                    $q->whereNull('context->app_source')
                      ->orWhereJsonContains('context->app_source', 'backend');
                })->whereNull('context->frontend')->count(),
                'admin' => ErrorLog::whereJsonContains('context->app_source', 'admin')->count(),
                'openpbbg' => ErrorLog::where(function($q) {
                    $q->whereJsonContains('context->app_source', 'openpbbg')
                      ->orWhere(function($subQ) {
                          $subQ->whereJsonContains('context->frontend', true)
                               ->whereNull('context->app_source');
                      });
                })->count(),
                'laravel_log' => ErrorLog::whereJsonContains('context->app_source', 'laravel-log')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }

    /**
     * Get Laravel log file information and recent entries
     */
    public function laravelLog(Request $request)
    {
        $logReader = new LaravelLogReader();

        $limit = $request->input('limit', 50);
        $entries = $logReader->parseLogFile($limit);
        $stats = $logReader->getStats();

        return response()->json([
            'success' => true,
            'entries' => $entries,
            'stats' => $stats,
        ]);
    }

    /**
     * Sync Laravel log file entries to database
     */
    public function syncLaravelLog(Request $request)
    {
        $logReader = new LaravelLogReader();

        $limit = $request->input('limit', 50);
        $imported = $logReader->syncToDatabase($limit);

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} new log entries",
            'imported' => $imported,
        ]);
    }

    /**
     * Clear the Laravel log file
     */
    public function clearLaravelLog()
    {
        $logReader = new LaravelLogReader();
        $cleared = $logReader->clearLog();

        return response()->json([
            'success' => $cleared,
            'message' => $cleared ? 'Log file cleared' : 'Failed to clear log file',
        ]);
    }
}
