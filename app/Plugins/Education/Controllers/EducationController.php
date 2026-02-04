<?php

namespace App\Plugins\Education\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Education\Models\EducationCourse;
use App\Plugins\Education\Models\UserEducation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    /**
     * Get all course enrollments
     */
    public function allEnrollments(Request $request): JsonResponse
    {
        $query = UserEducation::with(['user', 'course']);

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by user
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }

        $enrollments = $query->orderBy('started_at', 'desc')->paginate(50);

        return response()->json($enrollments);
    }

    /**
     * Get education statistics
     */
    public function statistics(): JsonResponse
    {
        $totalEnrollments = UserEducation::count();
        
        $stats = [
            'total_enrollments' => $totalEnrollments,
            'active_enrollments' => UserEducation::where('status', 'in_progress')->count(),
            'completed_courses' => UserEducation::where('status', 'completed')->count(),
            'cancelled_courses' => UserEducation::where('status', 'cancelled')->count(),
            'total_courses' => EducationCourse::count(),
            'completion_rate' => $totalEnrollments > 0 
                ? (UserEducation::where('status', 'completed')->count() / $totalEnrollments * 100)
                : 0,
            'enrollments_by_course' => UserEducation::with('course')
                ->get()
                ->groupBy('course.name')
                ->map(function($group) {
                    return [
                        'total' => $group->count(),
                        'completed' => $group->where('status', 'completed')->count(),
                        'in_progress' => $group->where('status', 'in_progress')->count(),
                        'cancelled' => $group->where('status', 'cancelled')->count(),
                    ];
                }),
            'average_progress' => UserEducation::where('status', 'in_progress')->avg('progress_percentage') ?? 0,
        ];

        return response()->json($stats);
    }
}
