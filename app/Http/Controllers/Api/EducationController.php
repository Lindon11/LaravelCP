<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EducationService;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    protected $educationService;

    public function __construct(EducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    public function index(Request $request)
    {
        $courses = $this->educationService->getAvailableCourses($request->user());
        return response()->json(['courses' => $courses]);
    }

    public function enroll(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:education_courses,id']);
        try {
            $enrollment = $this->educationService->enrollInCourse($request->user(), $request->course_id);
            return response()->json(['message' => 'Successfully enrolled!', 'enrollment' => $enrollment]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function progress(Request $request)
    {
        $enrollment = $this->educationService->checkProgress($request->user());
        if (!$enrollment) {
            return response()->json(['progress' => null, 'message' => 'Not enrolled in any course.']);
        }
        return response()->json(['progress' => $enrollment]);
    }

    public function history(Request $request)
    {
        $history = $this->educationService->getCourseHistory($request->user());
        return response()->json(['history' => $history]);
    }
}
