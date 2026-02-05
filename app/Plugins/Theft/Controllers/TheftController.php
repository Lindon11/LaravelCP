<?php

namespace App\Plugins\Theft\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Theft\Models\TheftType;
use App\Plugins\Racing\Models\Garage;
use App\Plugins\Theft\Services\TheftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheftController extends Controller
{
    protected $theftService;

    public function __construct(TheftService $theftService)
    {
        $this->theftService = $theftService;
    }

    /**
     * Display theft page with available theft types
     */
    public function index()
    {
        $player = Auth::user();
        
        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

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
        $player = Auth::user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        // Check cooldown
        if (!$this->theftService->canAttemptTheft($player)) {
            return redirect()->route('theft.index')
                ->with('error', 'You must wait before attempting another theft!');
        }

        // Check level requirement
        if ($player->level < $theftType->required_level) {
            return redirect()->route('theft.index')
                ->with('error', 'You are not high enough level for this theft type!');
        }

        // Attempt the theft
        $result = $this->theftService->attemptTheft($player, $theftType);

        if ($result['success']) {
            return redirect()->route('theft.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('theft.index')
                ->with('error', $result['message']);
        }
    }

    /**
     * Display garage
     */
    public function garage()
    {
        $player = Auth::user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        $garage = $this->theftService->getGarage($player);

        return response()->json([
            'player' => $player,
            'garage' => $garage,
        ]);
    }

    /**
     * Sell a car from garage
     */
    public function sell(Request $request, Garage $garage)
    {
        $player = Auth::user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        // Verify ownership
        if ($garage->user_id !== $player->id) {
            return redirect()->route('theft.garage')
                ->with('error', 'This is not your car!');
        }

        $value = $this->theftService->sellCar($player, $garage);

        return redirect()->route('theft.garage')
            ->with('success', "Sold car for $" . number_format($value) . "!");
    }
}
