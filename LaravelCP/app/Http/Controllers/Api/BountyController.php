<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BountyService;
use Illuminate\Http\Request;

class BountyController extends Controller
{
    public function __construct(
        protected BountyService $bountyService
    ) {}

    /**
     * Get bounty data
     */
    public function index(Request $request)
    {
        $player = $request->user();
        $bounties = $this->bountyService->getActiveBounties();
        $myBounties = $this->bountyService->getMyBounties($player);

        return response()->json([
            'player' => $player,
            'bounties' => $bounties,
            'myBounties' => $myBounties,
            'minAmount' => BountyService::MIN_BOUNTY_AMOUNT,
            'feePercentage' => BountyService::BOUNTY_FEE_PERCENTAGE,
        ]);
    }

    /**
     * Place a bounty on someone
     */
    public function place(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:' . BountyService::MIN_BOUNTY_AMOUNT,
            'reason' => 'nullable|string|max:255',
        ]);

        $player = $request->user();
        $target = User::findOrFail($request->target_id);

        try {
            $result = $this->bountyService->placeBounty($player, $target, $request->amount, $request->reason);
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
