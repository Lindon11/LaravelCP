<?php

namespace App\Plugins\Racing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Racing\Models\Car;
use Illuminate\Http\Request;

class CarManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Car::orderBy('required_level', 'asc')->get();
        return response()->json($cars);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|integer|min:0',
            'theft_chance' => 'required|integer|min:0|max:100',
            'required_level' => 'required|integer|min:1',
        ]);

        $car = Car::create($validated);
        return response()->json($car, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $car = Car::findOrFail($id);
        return response()->json($car);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $car = Car::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'sometimes|required|integer|min:0',
            'theft_chance' => 'sometimes|required|integer|min:0|max:100',
            'required_level' => 'sometimes|required|integer|min:1',
        ]);

        $car->update($validated);
        return response()->json($car);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::findOrFail($id);
        $car->delete();
        return response()->json(null, 204);
    }
}
