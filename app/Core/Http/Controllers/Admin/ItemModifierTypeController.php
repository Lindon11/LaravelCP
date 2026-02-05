<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Models\ItemModifierType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemModifierTypeController extends Controller
{
    public function index()
    {
        return ItemModifierType::orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:item_modifier_types,name',
            'label' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? ItemModifierType::max('sort_order') + 1;

        return ItemModifierType::create($validated);
    }

    public function show(ItemModifierType $itemModifierType)
    {
        return $itemModifierType;
    }

    public function update(Request $request, ItemModifierType $itemModifierType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:item_modifier_types,name,' . $itemModifierType->id,
            'label' => 'sometimes|string',
            'sort_order' => 'nullable|integer',
        ]);

        $itemModifierType->update($validated);
        return $itemModifierType;
    }

    public function destroy(ItemModifierType $itemModifierType)
    {
        $itemModifierType->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
