<?php

namespace App\Plugins\Properties\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Properties\Models\Property;
use Illuminate\Http\Request;

class PropertyManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::orderBy('required_level', 'asc')->get();
        return response()->json($properties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:house,apartment,mansion,warehouse,business,casino',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'income_per_day' => 'required|numeric|min:0',
            'required_level' => 'required|integer|min:1',
        ]);

        $property = Property::create($validated);
        return response()->json($property, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $property = Property::findOrFail($id);
        return response()->json($property);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $property = Property::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:house,apartment,mansion,warehouse,business,casino',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'income_per_day' => 'sometimes|required|numeric|min:0',
            'required_level' => 'sometimes|required|integer|min:1',
        ]);

        $property->update($validated);
        return response()->json($property);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $property = Property::findOrFail($id);
        $property->delete();
        return response()->json(null, 204);
    }
}
