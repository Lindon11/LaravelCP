<?php

namespace App\Modules\DailyRewards\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DailyRewardService;
use Illuminate\Http\Request;

class DailyRewardController extends Controller
{
    public function __construct(
        private DailyRewardService $dailyRewardService
    ) {}

    public function claim(Request $request)
    {
        try {
            $result = $this->dailyRewardService->claimReward($request->user());
            
            return back()->with('success', $result['message'] . " You received: $" . 
                number_format($result['rewards']['cash']) . ", " . 
                $result['rewards']['xp'] . " XP" . 
                ($result['rewards']['bullets'] > 0 ? ", " . $result['rewards']['bullets'] . " bullets" : ""));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
