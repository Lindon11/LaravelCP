<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DetectiveService;
use Illuminate\Http\Request;

class DetectiveController extends Controller
{
    public function __construct(
        protected DetectiveService $detectiveService
    ) {}

    /**
     * Get detective data
     */
    public function index(Request $request)
    {
        $player = $request->user();
        $reports = $this->detectiveService->getMyReports($player);

        return response()->json([
            'player' => $player,
            'reports' => $reports,
            'cost' => DetectiveService::DETECTIVE_COST,
            'investigationTime' => DetectiveService::INVESTIGATION_TIME / 60,
        ]);
    }

    /**
     * Hire a detective
     */
    public function hire(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:users,id',
        ]);

        $player = $request->user();
        $target = User::findOrFail($request->target_id);

        try {
            $result = $this->detectiveService->hireDetective($player, $target);
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
