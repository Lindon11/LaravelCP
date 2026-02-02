<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bounty;
use Illuminate\Http\Request;

class BountyController extends Controller
{
    public function index()
    {
        $bounties = Bounty::with(['target', 'placer', 'claimer'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($bounties);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:users,id',
            'placed_by' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string',
            'status' => 'required|in:active,claimed,expired',
        ]);

        $bounty = Bounty::create($validated);
        return response()->json($bounty->load(['target', 'placer']), 201);
    }

    public function show(Bounty $bounty)
    {
        return response()->json($bounty->load(['target', 'placer', 'claimer']));
    }

    public function update(Request $request, Bounty $bounty)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:users,id',
            'placed_by' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string',
            'status' => 'required|in:active,claimed,expired',
        ]);

        $bounty->update($validated);
        return response()->json($bounty->load(['target', 'placer', 'claimer']));
    }

    public function destroy(Bounty $bounty)
    {
        $bounty->delete();
        return response()->json(null, 204);
    }
}
