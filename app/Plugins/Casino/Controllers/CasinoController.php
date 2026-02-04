<?php

namespace App\Plugins\Casino\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Casino\Models\CasinoBet;
use App\Plugins\Casino\Models\CasinoGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CasinoController extends Controller
{
    /**
     * Get all casino bets
     */
    public function allBets(Request $request): JsonResponse
    {
        $query = CasinoBet::with(['user', 'game']);

        // Filter by game
        if ($request->filled('game_id')) {
            $query->where('game_id', $request->game_id);
        }

        // Filter by result
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('played_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('played_at', '<=', $request->date_to);
        }

        // Search by user
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }

        $bets = $query->orderBy('played_at', 'desc')->paginate(50);

        return response()->json($bets);
    }

    /**
     * Get casino statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_bets' => CasinoBet::count(),
            'total_wagered' => CasinoBet::sum('bet_amount'),
            'total_won' => CasinoBet::where('result', 'win')->sum('payout'),
            'total_lost' => CasinoBet::where('result', 'loss')->sum('bet_amount'),
            'house_profit' => CasinoBet::where('result', 'loss')->sum('bet_amount') - 
                             CasinoBet::where('result', 'win')->sum('payout'),
            'win_rate' => CasinoBet::where('result', 'win')->count() / 
                         max(CasinoBet::count(), 1) * 100,
            'bets_by_game' => CasinoBet::with('game')
                ->get()
                ->groupBy('game.name')
                ->map(function($group) {
                    return [
                        'total_bets' => $group->count(),
                        'total_wagered' => $group->sum('bet_amount'),
                        'total_won' => $group->where('result', 'win')->sum('payout'),
                        'win_rate' => $group->where('result', 'win')->count() / $group->count() * 100,
                    ];
                }),
            'recent_big_wins' => CasinoBet::with(['user', 'game'])
                ->where('result', 'win')
                ->where('payout', '>', 10000)
                ->orderBy('payout', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}
