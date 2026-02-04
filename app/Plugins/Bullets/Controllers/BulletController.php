<?php

namespace App\Plugins\Bullets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Bullets\Services\BulletService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BulletController extends Controller
{
    protected $bulletService;

    public function __construct(BulletService $bulletService)
    {
        $this->bulletService = $bulletService;
    }

    /**
     * Display bullet shop
     */
    public function index()
    {
        $player = auth()->user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        return Inertia::render('Modules/Bullets/Index', [
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

        $player = auth()->user();

        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found.');
        }

        $result = $this->bulletService->buyBullets($player, $request->quantity);

        if ($result['success']) {
            return redirect()->route('bullets.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('bullets.index')
                ->with('error', $result['message']);
        }
    }
}
