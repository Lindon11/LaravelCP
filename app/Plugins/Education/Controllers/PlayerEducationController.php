<?php

namespace App\Plugins\Education\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Education\Models\EducationCourse;
use App\Plugins\Education\Models\UserEducation;
use App\Plugins\Education\Services\EducationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerEducationController extends Controller
{
    protected EducationService $educationService;

    public function __construct(EducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    /**
     * Get public course catalog (no auth required)
     */
    public function catalog(): JsonResponse
    {
        $courses = EducationCourse::where('is_active', true)
            ->select('id', 'name', 'type', 'description', 'required_level', 'duration_hours', 'cost', 'intelligence_reward', 'endurance_reward')
            ->orderBy('required_level')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * Get available courses for the authenticated player
     */
    public function availableCourses(): JsonResponse
    {
        $user = Auth::user();
        $courses = $this->educationService->getAvailableCourses($user);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    /**
     * Show a single course with details
     */
    public function showCourse(int $course): JsonResponse
    {
        $course = EducationCourse::findOrFail($course);
        $user = Auth::user();

        $enrollment = UserEducation::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'enrollment' => $enrollment,
                'can_enroll' => $user->level >= $course->required_level
                    && $user->cash >= $course->cost
                    && !UserEducation::where('user_id', $user->id)->where('status', 'in_progress')->exists(),
            ],
        ]);
    }

    /**
     * Enroll in a course
     */
    public function enroll(int $course): JsonResponse
    {
        $user = Auth::user();

        try {
            $enrollment = $this->educationService->enrollInCourse($user, $course);

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in course.',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel current enrollment
     */
    public function cancel(): JsonResponse
    {
        /** @var \App\Core\Models\User $user */
        $user = Auth::user();

        $enrollment = UserEducation::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently enrolled in any course.',
            ], 404);
        }

        $enrollment->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        // Partial refund (50%)
        $course = $enrollment->course;
        $refund = (int) ($course->cost * 0.5);
        if ($refund > 0) {
            $user->increment('cash', $refund);
        }

        return response()->json([
            'success' => true,
            'message' => "Course cancelled. Refunded \${$refund}.",
            'data' => ['refund' => $refund],
        ]);
    }

    /**
     * Check progress on current course
     */
    public function progress(): JsonResponse
    {
        $user = Auth::user();
        $enrollment = $this->educationService->checkProgress($user);

        if (!$enrollment) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active enrollment.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $enrollment->load('course'),
        ]);
    }

    /**
     * Get course history
     */
    public function history(): JsonResponse
    {
        $user = Auth::user();
        $history = $this->educationService->getCourseHistory($user);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
