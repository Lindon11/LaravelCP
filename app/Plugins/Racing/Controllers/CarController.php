<?php

namespace App\Plugins\Racing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Racing\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index()
    {
        $cars = Car::orderBy('value', 'asc')->get();
        return response()->json($cars);
    }

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

    public function show(Car $car)
    {
        return response()->json($car);
    }

    public function update(Request $request, Car $car)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|integer|min:0',
            'theft_chance' => 'required|integer|min:0|max:100',
            'required_level' => 'required|integer|min:1',
        ]);

        $car->update($validated);
        return response()->json($car);
    }

    public function destroy(Car $car)
    {
        $car->delete();
        return response()->json(null, 204);
    }
}
