<?php

namespace App\Plugins\Education\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Education\Models\EducationCourse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = EducationCourse::withCount('enrollments')->orderBy('type')->orderBy('name')->get();
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:intelligence,endurance,mixed',
            'description' => 'required|string',
            'duration_hours' => 'required|integer|min:1',
            'cost' => 'required|integer|min:0',
            'intelligence_reward' => 'nullable|integer',
            'endurance_reward' => 'nullable|integer',
        ]);
        $course = EducationCourse::create($request->all());
        return response()->json(['message' => 'Course created', 'course' => $course], 201);
    }

    public function show($id)
    {
        $course = EducationCourse::with('enrollments.user')->findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, $id)
    {
        $course = EducationCourse::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:intelligence,endurance,mixed',
            'description' => 'sometimes|required|string',
            'duration_hours' => 'sometimes|required|integer|min:1',
            'cost' => 'sometimes|required|integer|min:0',
            'intelligence_reward' => 'nullable|integer',
            'endurance_reward' => 'nullable|integer',
        ]);
        
        $course->update($request->all());
        return response()->json(['message' => 'Course updated', 'course' => $course]);
    }

    public function destroy($id)
    {
        $course = EducationCourse::findOrFail($id);
        
        // Check if course has active enrollments
        if ($course->enrollments()->where('status', 'in_progress')->exists()) {
            return response()->json(['error' => 'Cannot delete course with active enrollments'], 400);
        }
        
        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
