<?php

namespace App\Modules\Properties\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $available = $this->propertyService->getAvailableProperties();
        $myProperties = $player ? $this->propertyService->getMyProperties($player) : collect();

        return Inertia::render('Modules/Properties/Index', [
            'player' => $player,
            'available' => $available,
            'myProperties' => $myProperties,
        ]);
    }

    public function buy(Request $request, Property $property)
    {
        $player = User::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $result = $this->propertyService->buyProperty($player, $property);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sell(Request $request, Property $property)
    {
        $player = User::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $result = $this->propertyService->sellProperty($player, $property);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function collectIncome(Request $request)
    {
        $player = User::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $result = $this->propertyService->collectIncome($player);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
