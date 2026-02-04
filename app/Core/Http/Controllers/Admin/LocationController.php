<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('name')->get();
        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'travel_cost' => 'required|numeric|min:0',
            'required_level' => 'required|integer|min:1',
            'image' => 'nullable|string'
        ]);

        $location = Location::create($validated);
        return response()->json($location, 201);
    }

    public function show(string $id)
    {
        $location = Location::findOrFail($id);
        return response()->json($location);
    }

    public function update(Request $request, string $id)
    {
        $location = Location::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'travel_cost' => 'numeric|min:0',
            'required_level' => 'integer|min:1',
            'image' => 'nullable|string'
        ]);

        $location->update($validated);
        return response()->json($location);
    }

    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return response()->json(null, 204);
    }
}
