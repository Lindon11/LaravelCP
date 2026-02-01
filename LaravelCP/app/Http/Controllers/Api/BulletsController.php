<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BulletService;
use Illuminate\Http\Request;

class BulletsController extends Controller
{
    public function __construct(
        protected BulletService $bulletService
    ) {}

    /**
     * Get bullets data
     */
    public function index(Request $request)
    {
        $player = $request->user();

        return response()->json([
            'player' => $player,
            'costPerBullet' => BulletService::COST_PER_BULLET,
        ]);
    }

    /**
     * Buy bullets
     */
    public function buy(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $player = $request->user();
        $result = $this->bulletService->buyBullets($player, $request->quantity);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }
    }
}
