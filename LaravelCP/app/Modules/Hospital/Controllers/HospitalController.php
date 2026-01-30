<?php

namespace App\Modules\Hospital\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HospitalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HospitalController extends Controller
{
    protected $hospitalService;

    public function __construct(HospitalService $hospitalService)
    {
        $this->hospitalService = $hospitalService;
    }

    /**
     * Display hospital page
     */
    public function index()
    {
        $player = auth()->user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        $fullHealCost = $this->hospitalService->calculateFullHealCost($player);

        return Inertia::render('Modules/Hospital/Index', [
            'player' => $player,
            'costPerHp' => HospitalService::COST_PER_HP,
            'fullHealCost' => $fullHealCost,
        ]);
    }

    /**
     * Heal by specific amount
     */
    public function heal(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $player = auth()->user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        $result = $this->hospitalService->heal($player, $request->amount);

        if ($result['success']) {
            return redirect()->route('hospital.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('hospital.index')
                ->with('error', $result['message']);
        }
    }

    /**
     * Heal to full health
     */
    public function healFull()
    {
        $player = auth()->user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        $result = $this->hospitalService->healFull($player);

        if ($result['success']) {
            return redirect()->route('hospital.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('hospital.index')
                ->with('error', $result['message']);
        }
    }
}
