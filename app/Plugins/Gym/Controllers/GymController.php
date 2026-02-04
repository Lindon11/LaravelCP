<?php

namespace App\Plugins\Gym\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Plugins\Gym\Services\GymService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GymController extends Controller
{
    public function __construct(
        protected GymService $gymService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $info = $this->gymService->getTrainingInfo();

        return Inertia::render('Modules/Gym/Index', [
            'player' => $player,
            'costs' => $info['costs'],
            'maxPerSession' => $info['max_per_session'],
        ]);
    }

    public function train(Request $request)
    {
        $request->validate([
            'attribute' => 'required|in:strength,defense,speed,stamina',
            'times' => 'required|integer|min:1|max:100',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $result = $this->gymService->train($player, $request->attribute, $request->times);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
