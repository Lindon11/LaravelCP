<?php

namespace App\Plugins\Properties\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Properties\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::orderBy('required_level', 'asc')->get();
        return response()->json($properties);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'income_per_day' => 'required|numeric|min:0',
            'required_level' => 'required|integer|min:1',
        ]);

        $property = Property::create($validated);
        return response()->json($property, 201);
    }

    public function show(Property $property)
    {
        return response()->json($property);
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'income_per_day' => 'required|numeric|min:0',
            'required_level' => 'required|integer|min:1',
        ]);

        $property->update($validated);
        return response()->json($property);
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return response()->json(null, 204);
    }
}
