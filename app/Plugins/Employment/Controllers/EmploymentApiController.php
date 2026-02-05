<?php

namespace App\Plugins\Employment\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Employment\EmploymentModule;
use App\Plugins\Employment\Models\Company;
use App\Plugins\Employment\Models\EmploymentPosition;
use App\Plugins\Employment\Models\UserEmployment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentApiController extends Controller
{
    protected EmploymentModule $employmentModule;

    public function __construct(EmploymentModule $employmentModule)
    {
        $this->employmentModule = $employmentModule;
    }

    /**
     * Get all active companies
     */
    public function companies(): JsonResponse
    {
        $companies = Company::where('is_active', true)
            ->withCount(['positions' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $companies,
        ]);
    }

    /**
     * Get positions for a company
     */
    public function positions(int $company): JsonResponse
    {
        $positions = EmploymentPosition::where('company_id', $company)
            ->where('is_active', true)
            ->orderBy('level_requirement')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $positions,
        ]);
    }

    /**
     * Get current employment status
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->employmentModule->getStats($user);

        $employment = $this->employmentModule->getCurrentEmployment($user);

        return response()->json([
            'success' => true,
            'data' => [
                'employment' => $employment?->load(['company', 'position']),
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Apply for a position
     */
    public function apply(int $position): JsonResponse
    {
        $user = Auth::user();
        $pos = EmploymentPosition::findOrFail($position);

        $result = $this->employmentModule->applyForJob($user, $pos);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Work at current job
     */
    public function work(): JsonResponse
    {
        $user = Auth::user();
        $result = $this->employmentModule->work($user);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Quit current job
     */
    public function quit(): JsonResponse
    {
        $user = Auth::user();
        $result = $this->employmentModule->quitJob($user);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get employment history
     */
    public function history(): JsonResponse
    {
        $user = Auth::user();

        $history = UserEmployment::where('user_id', $user->id)
            ->with(['company', 'position'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
