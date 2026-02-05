<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\CrimeDifficulty;
use Illuminate\Http\Request;

class CrimeDifficultyController extends Controller
{
    public function index()
    {
        return response()->json(CrimeDifficulty::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:crime_difficulties,name',
            'label' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? CrimeDifficulty::max('sort_order') + 1;
        return response()->json(CrimeDifficulty::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(CrimeDifficulty::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = CrimeDifficulty::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:50|unique:crime_difficulties,name,' . $item->id,
            'label' => 'string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        CrimeDifficulty::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
