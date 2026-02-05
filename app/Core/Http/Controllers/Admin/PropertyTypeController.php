<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function index()
    {
        return response()->json(PropertyType::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:property_types,name',
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? PropertyType::max('sort_order') + 1;
        return response()->json(PropertyType::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(PropertyType::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = PropertyType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:50|unique:property_types,name,' . $item->id,
            'label' => 'string|max:100',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        PropertyType::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
