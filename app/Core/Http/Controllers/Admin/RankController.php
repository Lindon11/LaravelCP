<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Rank;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::orderBy('required_exp')->get();
        return response()->json($ranks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'required_exp' => 'required|integer|min:0',
            'max_health' => 'required|integer|min:1',
            'cash_reward' => 'nullable|integer|min:0',
            'bullet_reward' => 'nullable|integer|min:0',
            'user_limit' => 'nullable|integer|min:0'
        ]);

        $rank = Rank::create($validated);
        return response()->json($rank, 201);
    }

    public function show(string $id)
    {
        $rank = Rank::findOrFail($id);
        return response()->json($rank);
    }

    public function update(Request $request, string $id)
    {
        $rank = Rank::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'required_exp' => 'integer|min:0',
            'max_health' => 'integer|min:1',
            'cash_reward' => 'nullable|integer|min:0',
            'bullet_reward' => 'nullable|integer|min:0',
            'user_limit' => 'nullable|integer|min:0'
        ]);

        $rank->update($validated);
        return response()->json($rank);
    }

    public function destroy(string $id)
    {
        $rank = Rank::findOrFail($id);
        $rank->delete();
        return response()->json(null, 204);
    }
}
