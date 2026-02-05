<?php

namespace App\Plugins\Bullets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Bullets\Services\BulletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletApiController extends Controller
{
    protected BulletService $bulletService;

    public function __construct(BulletService $bulletService)
    {
        $this->bulletService = $bulletService;
    }

    /**
     * Get player's bullet info
     */
    public function index(): JsonResponse
    {
        $player = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'bullets' => $player->bullets ?? 0,
                'cost_per_bullet' => BulletService::COST_PER_BULLET,
            ],
        ]);
    }

    /**
     * Buy bullets
     */
    public function buy(Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10000',
        ]);

        $player = Auth::user();
        $result = $this->bulletService->buyBullets($player, $request->quantity);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
