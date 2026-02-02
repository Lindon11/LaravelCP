<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mission::query();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        return $query->orderBy('order')->orderBy('id')->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:one_time,daily,weekly,repeatable',
            'required_level' => 'nullable|integer|min:1',
            'required_location_id' => 'nullable|exists:locations,id',
            'objective_type' => 'nullable|string',
            'objective_count' => 'nullable|integer|min:1',
            'objective_data' => 'nullable|array',
            'cash_reward' => 'nullable|integer|min:0',
            'respect_reward' => 'nullable|integer|min:0',
            'experience_reward' => 'nullable|integer|min:0',
            'item_reward_id' => 'nullable|exists:items,id',
            'item_reward_quantity' => 'nullable|integer|min:1',
            'cooldown_hours' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $mission = Mission::create($validated);

        return response()->json($mission, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Mission $mission)
    {
        return response()->json($mission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mission $mission)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:one_time,daily,weekly,repeatable',
            'required_level' => 'nullable|integer|min:1',
            'required_location_id' => 'nullable|exists:locations,id',
            'objective_type' => 'nullable|string',
            'objective_count' => 'nullable|integer|min:1',
            'objective_data' => 'nullable|array',
            'cash_reward' => 'nullable|integer|min:0',
            'respect_reward' => 'nullable|integer|min:0',
            'experience_reward' => 'nullable|integer|min:0',
            'item_reward_id' => 'nullable|exists:items,id',
            'item_reward_quantity' => 'nullable|integer|min:1',
            'cooldown_hours' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $mission->update($validated);

        return response()->json($mission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mission $mission)
    {
        $mission->delete();

        return response()->json(['message' => 'Mission deleted successfully']);
    }
}
