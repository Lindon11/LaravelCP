<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
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

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        // Get error types for filtering
        $errorTypes = ErrorLog::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        // Statistics
        $stats = [
            'total' => ErrorLog::count(),
            'unresolved' => ErrorLog::where('resolved', false)->count(),
            'unique_types' => ErrorLog::distinct('type')->count(),
            'last_24h' => ErrorLog::where('last_seen_at', '>=', now()->subDay())->count(),
        ];

        return response()->json([
            'success' => true,
            'errors' => $errors,
            'error_types' => $errorTypes,
            'statistics' => $stats,
        ]);
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
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats,
        ]);
    }
}
