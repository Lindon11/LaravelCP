<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemManagementController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('name')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'sell_price' => 'nullable|integer|min:0',
            'tradeable' => 'boolean',
            'stackable' => 'boolean',
            'max_stack' => 'nullable|integer|min:1',
            'rarity' => 'required|string|in:common,uncommon,rare,epic,legendary'
        ]);

        $item = Item::create($validated);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'string',
            'description' => 'string',
            'image' => 'nullable|string',
            'price' => 'integer|min:0',
            'sell_price' => 'nullable|integer|min:0',
            'tradeable' => 'boolean',
            'stackable' => 'boolean',
            'max_stack' => 'nullable|integer|min:1',
            'rarity' => 'string|in:common,uncommon,rare,epic,legendary'
        ]);

        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
