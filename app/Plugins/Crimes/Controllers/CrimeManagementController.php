<?php

namespace App\Plugins\Crimes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Crimes\Models\Crime;
use Illuminate\Http\Request;

class CrimeManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $crimes = Crime::orderBy('required_level')->orderBy('difficulty')->get();
        return response()->json($crimes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'success_rate' => 'required|integer|min:1|max:100',
            'min_cash' => 'required|integer|min:0',
            'max_cash' => 'required|integer|min:0',
            'experience_reward' => 'required|integer|min:0',
            'respect_reward' => 'required|integer|min:0',
            'cooldown_seconds' => 'required|integer|min:0',
            'energy_cost' => 'required|integer|min:1',
            'required_level' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'active' => 'boolean'
        ]);

        $crime = Crime::create($validated);
        return response()->json($crime, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $crime = Crime::findOrFail($id);
        return response()->json($crime);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $crime = Crime::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'success_rate' => 'integer|min:1|max:100',
            'min_cash' => 'integer|min:0',
            'max_cash' => 'integer|min:0',
            'experience_reward' => 'integer|min:0',
            'respect_reward' => 'integer|min:0',
            'cooldown_seconds' => 'integer|min:0',
            'energy_cost' => 'integer|min:1',
            'required_level' => 'integer|min:1',
            'difficulty' => 'in:easy,medium,hard',
            'active' => 'boolean'
        ]);

        $crime->update($validated);
        return response()->json($crime);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $crime = Crime::findOrFail($id);
        $crime->delete();
        return response()->json(null, 204);
    }
}
