<?php

namespace App\Plugins\OrganizedCrime\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\OrganizedCrimes\Models\OrganizedCrime;
use Illuminate\Http\Request;

class OrganizedCrimeController extends Controller
{
    public function index()
    {
        $crimes = OrganizedCrime::orderBy('required_members', 'asc')->get();
        return response()->json($crimes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'success_rate' => 'required|numeric|min:0|max:100',
            'min_reward' => 'required|numeric|min:0',
            'max_reward' => 'required|numeric|min:0',
            'required_members' => 'required|integer|min:1',
            'required_level' => 'required|integer|min:1',
            'cooldown' => 'required|integer|min:0',
        ]);

        $crime = OrganizedCrime::create($validated);
        return response()->json($crime, 201);
    }

    public function show(OrganizedCrime $organizedCrime)
    {
        return response()->json($organizedCrime);
    }

    public function update(Request $request, OrganizedCrime $organizedCrime)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'success_rate' => 'required|numeric|min:0|max:100',
            'min_reward' => 'required|numeric|min:0',
            'max_reward' => 'required|numeric|min:0',
            'required_members' => 'required|integer|min:1',
            'required_level' => 'required|integer|min:1',
            'cooldown' => 'required|integer|min:0',
        ]);

        $organizedCrime->update($validated);
        return response()->json($organizedCrime);
    }

    public function destroy(OrganizedCrime $organizedCrime)
    {
        $organizedCrime->delete();
        return response()->json(null, 204);
    }
}
