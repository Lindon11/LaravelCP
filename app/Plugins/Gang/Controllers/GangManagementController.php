<?php

namespace App\Plugins\Gang\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Gang\Models\Gang;
use Illuminate\Http\Request;

class GangManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gangs = Gang::with(['leader', 'members'])
            ->orderBy('level', 'desc')
            ->get();
        return response()->json($gangs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gangs,name',
            'tag' => 'required|string|max:10|unique:gangs,tag',
            'description' => 'nullable|string',
            'leader_id' => 'required|exists:users,id',
            'max_members' => 'nullable|integer|min:1',
            'level' => 'nullable|integer|min:1',
        ]);

        $validated['bank'] = 0;
        $validated['respect'] = 0;
        $validated['max_members'] = $validated['max_members'] ?? 10;
        $validated['level'] = $validated['level'] ?? 1;

        $gang = Gang::create($validated);
        return response()->json($gang->load('leader'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gang = Gang::with(['leader', 'members'])->findOrFail($id);
        return response()->json($gang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $gang = Gang::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:gangs,name,' . $id,
            'tag' => 'sometimes|required|string|max:10|unique:gangs,tag,' . $id,
            'description' => 'nullable|string',
            'leader_id' => 'sometimes|exists:users,id',
            'bank' => 'nullable|integer|min:0',
            'respect' => 'nullable|integer|min:0',
            'max_members' => 'nullable|integer|min:1',
            'level' => 'nullable|integer|min:1',
        ]);

        $gang->update($validated);
        return response()->json($gang->load(['leader', 'members']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $gang = Gang::findOrFail($id);
        $gang->delete();
        return response()->json(null, 204);
    }
}
