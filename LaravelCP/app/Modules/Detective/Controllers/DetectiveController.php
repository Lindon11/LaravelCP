<?php

namespace App\Modules\Detective\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DetectiveService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DetectiveController extends Controller
{
    public function __construct(
        protected DetectiveService $detectiveService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $reports = $player ? $this->detectiveService->getMyReports($player) : collect();

        return Inertia::render('Modules/Detective/Index', [
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
