<?php

namespace App\Modules\Bounty\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BountyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BountyController extends Controller
{
    public function __construct(
        protected BountyService $bountyService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $bounties = $this->bountyService->getActiveBounties();
        $myBounties = $player ? $this->bountyService->getMyBounties($player) : null;

        return Inertia::render('Modules/Bounty/Index', [
            'player' => $player,
            'bounties' => $bounties,
            'myBounties' => $myBounties,
            'minAmount' => BountyService::MIN_BOUNTY_AMOUNT,
            'feePercentage' => BountyService::BOUNTY_FEE_PERCENTAGE,
        ]);
    }

    public function place(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:players,id',
            'amount' => 'required|numeric|min:' . BountyService::MIN_BOUNTY_AMOUNT,
            'reason' => 'nullable|string|max:255',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();
        $target = User::findOrFail($request->target_id);

        try {
            $result = $this->bountyService->placeBounty($player, $target, $request->amount, $request->reason);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
