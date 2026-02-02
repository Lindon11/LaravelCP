<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertiesController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService
    ) {}

    /**
     * Get properties data
     */
    public function index(Request $request)
    {
        $player = $request->user();
        $available = $this->propertyService->getAvailableProperties();
        $myProperties = $this->propertyService->getMyProperties($player);

        return response()->json([
            'player' => $player,
            'available' => $available,
            'myProperties' => $myProperties,
        ]);
    }

    /**
     * Buy a property
     */
    public function buy(Request $request, Property $property)
    {
        $player = $request->user();

        try {
            $result = $this->propertyService->buyProperty($player, $property);
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

    /**
     * Sell a property
     */
    public function sell(Request $request, Property $property)
    {
        $player = $request->user();

        try {
            $result = $this->propertyService->sellProperty($player, $property);
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

    /**
     * Collect income from properties
     */
    public function collect(Request $request)
    {
        $player = $request->user();

        try {
            $result = $this->propertyService->collectIncome($player);
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'income' => $result['income'] ?? 0,
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
