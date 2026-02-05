<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\ItemRarity;
use Illuminate\Http\Request;

class ItemRarityController extends Controller
{
    public function index()
    {
        return response()->json(ItemRarity::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:item_rarities,name',
            'label' => 'required|string|max:100',
            'color' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? ItemRarity::max('sort_order') + 1;
        return response()->json(ItemRarity::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(ItemRarity::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = ItemRarity::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:50|unique:item_rarities,name,' . $item->id,
            'label' => 'string|max:100',
            'color' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        ItemRarity::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
