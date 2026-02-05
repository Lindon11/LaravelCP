<?php

namespace App\Plugins\Theft\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Theft\Models\TheftType;
use Illuminate\Http\Request;

class TheftTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $theftTypes = TheftType::orderBy('required_level', 'asc')->get();
        return response()->json($theftTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'success_rate' => 'required|integer|min:1|max:100',
            'jail_multiplier' => 'nullable|numeric|min:0',
            'min_car_value' => 'required|integer|min:0',
            'max_car_value' => 'required|integer|min:0',
            'max_damage' => 'nullable|integer|min:0|max:100',
            'cooldown' => 'nullable|integer|min:0',
            'required_level' => 'required|integer|min:1',
        ]);

        $theftType = TheftType::create($validated);
        return response()->json($theftType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $theftType = TheftType::findOrFail($id);
        return response()->json($theftType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $theftType = TheftType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'success_rate' => 'sometimes|required|integer|min:1|max:100',
            'jail_multiplier' => 'nullable|numeric|min:0',
            'min_car_value' => 'sometimes|required|integer|min:0',
            'max_car_value' => 'sometimes|required|integer|min:0',
            'max_damage' => 'nullable|integer|min:0|max:100',
            'cooldown' => 'nullable|integer|min:0',
            'required_level' => 'sometimes|required|integer|min:1',
        ]);

        $theftType->update($validated);
        return response()->json($theftType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $theftType = TheftType::findOrFail($id);
        $theftType->delete();
        return response()->json(null, 204);
    }
}
