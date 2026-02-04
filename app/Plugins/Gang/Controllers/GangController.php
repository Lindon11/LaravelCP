<?php

namespace App\Plugins\Gang\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Gang\Models\Gang;
use Illuminate\Http\Request;

class GangController extends Controller
{
    public function index()
    {
        $gangs = Gang::with('leader')
            ->orderBy('respect', 'desc')
            ->get();
        return response()->json($gangs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gangs',
            'tag' => 'required|string|max:10|unique:gangs',
            'description' => 'nullable|string',
            'logo' => 'nullable|url',
            'leader_id' => 'required|exists:users,id',
            'bank' => 'required|integer|min:0',
            'respect' => 'required|integer|min:0',
            'max_members' => 'required|integer|min:1',
            'level' => 'required|integer|min:1',
        ]);

        $gang = Gang::create($validated);
        return response()->json($gang->load('leader'), 201);
    }

    public function show(Gang $gang)
    {
        return response()->json($gang->load('leader'));
    }

    public function update(Request $request, Gang $gang)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gangs,name,' . $gang->id,
            'tag' => 'required|string|max:10|unique:gangs,tag,' . $gang->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|url',
            'leader_id' => 'required|exists:users,id',
            'bank' => 'required|integer|min:0',
            'respect' => 'required|integer|min:0',
            'max_members' => 'required|integer|min:1',
            'level' => 'required|integer|min:1',
        ]);

        $gang->update($validated);
        return response()->json($gang->load('leader'));
    }

    public function destroy(Gang $gang)
    {
        $gang->delete();
        return response()->json(null, 204);
    }
}
