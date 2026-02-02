<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasinoGame;
use Illuminate\Http\Request;

class CasinoGameController extends Controller
{
    public function index()
    {
        $games = CasinoGame::withCount('bets')->orderBy('type')->orderBy('name')->get();
        return response()->json($games);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:slots,roulette,blackjack,poker,dice',
            'description' => 'required|string',
            'min_bet' => 'required|integer|min:1',
            'max_bet' => 'required|integer',
            'house_edge' => 'required|numeric|min:0|max:100',
            'return_to_player' => 'required|numeric|min:0|max:100',
        ]);
        $game = CasinoGame::create($request->all());
        return response()->json(['message' => 'Game created', 'game' => $game], 201);
    }

    public function show($id)
    {
        $game = CasinoGame::with('bets')->findOrFail($id);
        return response()->json($game);
    }

    public function update(Request $request, $id)
    {
        $game = CasinoGame::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:slots,roulette,dice,blackjack',
            'description' => 'sometimes|required|string',
            'min_bet' => 'sometimes|required|integer|min:1',
            'max_bet' => 'sometimes|required|integer|gt:min_bet',
            'house_edge' => 'sometimes|required|numeric|min:0|max:100',
            'active' => 'sometimes|boolean',
        ]);
        
        $game->update($request->all());
        return response()->json(['message' => 'Game updated', 'game' => $game]);
    }

    public function destroy($id)
    {
        $game = CasinoGame::findOrFail($id);
        
        // Check if game has recent bets
        if ($game->bets()->where('created_at', '>', now()->subDays(30))->exists()) {
            return response()->json(['error' => 'Cannot delete game with recent bets'], 400);
        }
        
        $game->delete();
        return response()->json(['message' => 'Game deleted']);
    }
}
