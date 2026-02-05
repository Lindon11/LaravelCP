<?php

namespace App\Plugins\Detective\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Plugins\Detective\Services\DetectiveService;
use Illuminate\Http\Request;

class DetectiveController extends Controller
{
    public function __construct(
        protected DetectiveService $detectiveService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $reports = $player ? $this->detectiveService->getMyReports($player) : collect();

        return response()->json([
            'player' => $player,
            'reports' => $reports,
            'cost' => DetectiveService::DETECTIVE_COST,
            'investigationTime' => DetectiveService::INVESTIGATION_TIME / 60,
        ]);
    }

    public function hire(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:players,id',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();
        $target = User::findOrFail($request->target_id);

        try {
            $result = $this->detectiveService->hireDetective($player, $target);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
