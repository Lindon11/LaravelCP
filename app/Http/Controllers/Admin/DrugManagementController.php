<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use Illuminate\Http\Request;

class DrugManagementController extends Controller
{
    public function index()
    {
        $drugs = Drug::orderBy('name')->get();
        return response()->json($drugs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'base_price' => 'required|integer|min:0',
            'min_price' => 'required|integer|min:0',
            'max_price' => 'required|integer|min:0',
            'bust_chance' => 'required|numeric|min:0|max:1',
            'image' => 'nullable|string'
        ]);

        $drug = Drug::create($validated);
        return response()->json($drug, 201);
    }

    public function show(string $id)
    {
        $drug = Drug::findOrFail($id);
        return response()->json($drug);
    }

    public function update(Request $request, string $id)
    {
        $drug = Drug::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'base_price' => 'integer|min:0',
            'min_price' => 'integer|min:0',
            'max_price' => 'integer|min:0',
            'bust_chance' => 'numeric|min:0|max:1',
            'image' => 'nullable|string'
        ]);

        $drug->update($validated);
        return response()->json($drug);
    }

    public function destroy(string $id)
    {
        $drug = Drug::findOrFail($id);
        $drug->delete();
        return response()->json(null, 204);
    }
}
