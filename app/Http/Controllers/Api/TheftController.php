<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TheftType;
use App\Services\TheftService;
use Illuminate\Http\Request;

class TheftController extends Controller
{
    public function __construct(
        protected TheftService $theftService
    ) {}

    /**
     * Get theft page data
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $theftTypes = TheftType::where('required_level', '<=', $player->level)
            ->orderBy('success_rate', 'desc')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description,
                    'success_rate' => $type->success_rate,
                    'min_car_value' => $type->min_car_value,
                    'max_car_value' => $type->max_car_value,
                    'cooldown' => $type->cooldown,
                    'required_level' => $type->required_level,
                ];
            });

        $canAttempt = $this->theftService->canAttemptTheft($player);
        $cooldown = $this->theftService->getRemainingCooldown($player);

        return response()->json([
            'player' => $player,
            'theftTypes' => $theftTypes,
            'canAttempt' => $canAttempt,
            'cooldownRemaining' => $cooldown,
        ]);
    }

    /**
     * Attempt to steal a car
     */
    public function attempt(Request $request, TheftType $theftType)
    {
        $player = $request->user();

        // Check cooldown
        if (!$this->theftService->canAttemptTheft($player)) {
            return response()->json([
                'success' => false,
                'message' => 'You must wait before attempting another theft!',
                'cooldown' => $this->theftService->getRemainingCooldown($player),
            ], 400);
        }

        // Check level requirement
        if ($player->level < $theftType->required_level) {
            return response()->json([
                'success' => false,
                'message' => 'You are not high enough level for this theft type!',
            ], 400);
        }

        // Attempt the theft
        $result = $this->theftService->attemptTheft($player, $theftType);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'car' => $result['car'] ?? null,
            'player' => $player->fresh(),
            'cooldown' => $this->theftService->getRemainingCooldown($player),
        ]);
    }

    /**
     * Get player's garage
     */
    public function garage(Request $request)
    {
        $player = $request->user();
        $garage = $player->garage ?? [];

        return response()->json([
            'player' => $player,
            'garage' => $garage,
        ]);
    }

    /**
     * Sell a car from garage
     */
    public function sell(Request $request, $garageId)
    {
        $player = $request->user();

        try {
            $result = $this->theftService->sellCar($player, $garageId);
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
