<?php

namespace App\Plugins\Bounty\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Bounty\Models\Bounty;
use Illuminate\Http\Request;

class BountyManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bounties = Bounty::with(['target', 'placer', 'claimer'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($bounties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:users,id',
            'placed_by' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,claimed,cancelled',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $bounty = Bounty::create($validated);
        return response()->json($bounty->load(['target', 'placer']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bounty = Bounty::with(['target', 'placer', 'claimer'])->findOrFail($id);
        return response()->json($bounty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bounty = Bounty::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric|min:1',
            'reason' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,claimed,cancelled',
        ]);

        $bounty->update($validated);
        return response()->json($bounty->load(['target', 'placer', 'claimer']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bounty = Bounty::findOrFail($id);
        $bounty->delete();
        return response()->json(null, 204);
    }
}
