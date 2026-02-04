<?php

namespace App\Plugins\Employment\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Employment\Models\Company;
use App\Plugins\Employment\Models\EmploymentPosition;
use App\Plugins\Employment\Models\UserEmployment;
use App\Plugins\Employment\Models\WorkShift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmploymentController extends Controller
{
    /**
     * Get all employees with their current jobs
     */
    public function allEmployees(Request $request): JsonResponse
    {
        $query = UserEmployment::with(['user', 'position.company'])
            ->where('status', 'active');

        // Filter by company
        if ($request->filled('company_id')) {
            $query->whereHas('position', function($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('job_position_id', $request->position_id);
        }

        // Search by user
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }

        $employees = $query->orderBy('hired_at', 'desc')->paginate(50);

        return response()->json($employees);
    }

    /**
     * Get employment statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_employees' => UserEmployment::where('is_active', true)->count(),
            'total_positions' => EmploymentPosition::count(),
            'total_companies' => Company::count(),
            'average_performance' => UserEmployment::where('is_active', true)->avg('performance_rating'),
            'total_shifts_today' => WorkShift::whereDate('worked_at', today())->count(),
            'employees_by_company' => UserEmployment::with('company')
                ->where('is_active', true)
                ->get()
                ->groupBy('company.name')
                ->map(function($group) {
                    return $group->count();
                }),
            'total_salaries_today' => WorkShift::whereDate('worked_at', today())->sum('earnings'),
            'active_positions' => EmploymentPosition::where('is_active', true)->count(),
        ];

        return response()->json($stats);
    }
}
