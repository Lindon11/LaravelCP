<?php

namespace App\Plugins\Achievements\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Achievements\Models\Achievement;
use Illuminate\Http\Request;

class AchievementManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $achievements = Achievement::orderBy('sort_order', 'asc')->get();
        return response()->json($achievements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'requirement' => 'required|integer|min:1',
            'reward_cash' => 'nullable|integer|min:0',
            'reward_xp' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $achievement = Achievement::create($validated);
        return response()->json($achievement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $achievement = Achievement::findOrFail($id);
        return response()->json($achievement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $achievement = Achievement::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|string|max:255',
            'requirement' => 'sometimes|required|integer|min:1',
            'reward_cash' => 'nullable|integer|min:0',
            'reward_xp' => 'nullable|integer|min:0',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $achievement->update($validated);
        return response()->json($achievement);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->delete();
        return response()->json(null, 204);
    }
}
