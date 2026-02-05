<?php

namespace App\Plugins\Racing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Racing\Models\Race;
use Illuminate\Http\Request;

class RaceManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $races = Race::with(['participants', 'winner'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($races);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'required|numeric|min:0',
            'prize_pool' => 'required|numeric|min:0',
            'min_participants' => 'required|integer|min:2',
            'max_participants' => 'required|integer|min:2',
            'status' => 'nullable|string|in:waiting,racing,finished,cancelled',
        ]);

        $validated['status'] = $validated['status'] ?? 'waiting';

        $race = Race::create($validated);
        return response()->json($race, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $race = Race::with(['participants', 'winner'])->findOrFail($id);
        return response()->json($race);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $race = Race::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'entry_fee' => 'sometimes|required|numeric|min:0',
            'prize_pool' => 'sometimes|required|numeric|min:0',
            'min_participants' => 'sometimes|required|integer|min:2',
            'max_participants' => 'sometimes|required|integer|min:2',
            'status' => 'nullable|string|in:waiting,racing,finished,cancelled',
        ]);

        $race->update($validated);
        return response()->json($race->load(['participants', 'winner']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $race = Race::findOrFail($id);
        $race->delete();
        return response()->json(null, 204);
    }
}
