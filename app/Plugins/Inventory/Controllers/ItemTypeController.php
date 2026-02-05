<?php

namespace App\Plugins\Inventory\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\ItemType;
use Illuminate\Http\Request;

class ItemTypeController extends Controller
{
    public function index()
    {
        $types = ItemType::orderBy('sort_order')->orderBy('name')->get();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:item_types,name|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.regex' => 'The type key must be lowercase letters, numbers, and underscores only.',
        ]);

        $validated['icon'] = $validated['icon'] ?? 'CubeIcon';
        $validated['sort_order'] = $validated['sort_order'] ?? ItemType::max('sort_order') + 1;

        $type = ItemType::create($validated);
        return response()->json($type, 201);
    }

    public function show(string $id)
    {
        $type = ItemType::findOrFail($id);
        return response()->json($type);
    }

    public function update(Request $request, string $id)
    {
        $type = ItemType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:50|unique:item_types,name,' . $type->id . '|regex:/^[a-z0-9_]+$/',
            'label' => 'string|max:100',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.regex' => 'The type key must be lowercase letters, numbers, and underscores only.',
        ]);

        $type->update($validated);
        return response()->json($type);
    }

    public function destroy(string $id)
    {
        $type = ItemType::findOrFail($id);

        // Check if any items use this type
        $itemCount = \App\Core\Models\Item::where('type', $type->name)->count();
        if ($itemCount > 0) {
            return response()->json([
                'message' => "Cannot delete this type — {$itemCount} item(s) still use it."
            ], 422);
        }

        $type->delete();
        return response()->json(null, 204);
    }
}
