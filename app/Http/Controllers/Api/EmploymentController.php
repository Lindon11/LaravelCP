<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmploymentService;
use Illuminate\Http\Request;

class EmploymentController extends Controller
{
    protected $employmentService;

    public function __construct(EmploymentService $employmentService)
    {
        $this->employmentService = $employmentService;
    }

    public function index(Request $request)
    {
        $positions = $this->employmentService->getAvailablePositions($request->user());
        return response()->json(['positions' => $positions]);
    }

    public function currentJob(Request $request)
    {
        $job = $this->employmentService->getCurrentJob($request->user());
        
        $hasWorkedToday = false;
        if ($job && isset($job['last_work_at'])) {
            $hasWorkedToday = \Carbon\Carbon::parse($job['last_work_at'])->isToday();
        }
        
        return response()->json([
            'employment' => $job,
            'has_worked_today' => $hasWorkedToday
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate(['position_id' => 'required|exists:employment_positions,id']);
        try {
            $employment = $this->employmentService->applyForJob($request->user(), $request->position_id);
            return response()->json(['message' => 'Successfully hired!', 'employment' => $employment]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function work(Request $request)
    {
        try {
            $result = $this->employmentService->work($request->user());
            return response()->json(['message' => 'Work shift completed!', ...$result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function quit(Request $request)
    {
        try {
            $this->employmentService->quitJob($request->user());
            return response()->json(['message' => 'You have quit your job.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
