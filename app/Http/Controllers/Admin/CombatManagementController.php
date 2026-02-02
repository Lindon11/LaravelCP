<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CombatLocation;
use App\Models\CombatArea;
use App\Models\CombatEnemy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CombatManagementController extends Controller
{
    /**
     * Get all combat locations
     */
    public function getLocations()
    {
        $locations = CombatLocation::orderBy('name')->get();
        return response()->json(['data' => $locations]);
    }

    /**
     * Create a new combat location
     */
    public function createLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|url',
            'min_level' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = CombatLocation::create($request->all());
        return response()->json(['data' => $location], 201);
    }

    /**
     * Update a combat location
     */
    public function updateLocation(Request $request, $id)
    {
        $location = CombatLocation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|url',
            'min_level' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location->update($request->all());
        return response()->json(['data' => $location]);
    }

    /**
     * Delete a combat location
     */
    public function deleteLocation($id)
    {
        $location = CombatLocation::findOrFail($id);
        $location->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }

    /**
     * Get combat areas (optionally filtered by location)
     */
    public function getAreas(Request $request)
    {
        $query = CombatArea::with('location');

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $areas = $query->orderBy('difficulty')->get();
        return response()->json(['data' => $areas]);
    }

    /**
     * Create a new combat area
     */
    public function createArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:combat_locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|integer|min:1|max:5',
            'min_level' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $area = CombatArea::create($request->all());
        return response()->json(['data' => $area], 201);
    }

    /**
     * Update a combat area
     */
    public function updateArea(Request $request, $id)
    {
        $area = CombatArea::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:combat_locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|integer|min:1|max:5',
            'min_level' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $area->update($request->all());
        return response()->json(['data' => $area]);
    }

    /**
     * Delete a combat area
     */
    public function deleteArea($id)
    {
        $area = CombatArea::findOrFail($id);
        $area->delete();
        return response()->json(['message' => 'Area deleted successfully']);
    }

    /**
     * Get combat enemies (optionally filtered by area)
     */
    public function getEnemies(Request $request)
    {
        $query = CombatEnemy::with(['area', 'area.location']);

        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        $enemies = $query->orderBy('level')->get();
        return response()->json(['data' => $enemies]);
    }

    /**
     * Create a new combat enemy
     */
    public function createEnemy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:combat_areas,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'health' => 'required|integer|min:1',
            'max_health' => 'required|integer|min:1',
            'strength' => 'required|integer|min:0',
            'defense' => 'required|integer|min:0',
            'speed' => 'required|integer|min:0',
            'agility' => 'required|integer|min:0',
            'weakness' => 'nullable|string|max:255',
            'difficulty' => 'nullable|integer|min:1|max:5',
            'experience_reward' => 'required|integer|min:0',
            'cash_reward_min' => 'required|integer|min:0',
            'cash_reward_max' => 'required|integer|min:0',
            'spawn_rate' => 'nullable|numeric|min:0.01|max:1.00',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Remove icon from request as it's not in the database table
        $data = $request->except('icon');
        $enemy = CombatEnemy::create($data);
        return response()->json(['data' => $enemy], 201);
    }

    /**
     * Update a combat enemy
     */
    public function updateEnemy(Request $request, $id)
    {
        $enemy = CombatEnemy::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:combat_areas,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|integer|min:1',
            'health' => 'required|integer|min:1',
            'max_health' => 'required|integer|min:1',
            'strength' => 'required|integer|min:0',
            'defense' => 'required|integer|min:0',
            'speed' => 'required|integer|min:0',
            'agility' => 'required|integer|min:0',
            'weakness' => 'nullable|string|max:255',
            'difficulty' => 'nullable|integer|min:1|max:5',
            'experience_reward' => 'required|integer|min:0',
            'cash_reward_min' => 'required|integer|min:0',
            'cash_reward_max' => 'required|integer|min:0',
            'spawn_rate' => 'nullable|numeric|min:0.01|max:1.00',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Remove icon from request as it's not in the database table
        $data = $request->except('icon');
        $enemy->update($data);
        return response()->json(['data' => $enemy]);
    }

    /**
     * Delete a combat enemy
     */
    public function deleteEnemy($id)
    {
        $enemy = CombatEnemy::findOrFail($id);
        $enemy->delete();
        return response()->json(['message' => 'Enemy deleted successfully']);
    }
}
