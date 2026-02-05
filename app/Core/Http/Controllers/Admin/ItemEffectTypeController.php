<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Models\ItemEffectType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemEffectTypeController extends Controller
{
    public function index()
    {
        return ItemEffectType::orderBy('sort_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:item_effect_types,name',
            'label' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? ItemEffectType::max('sort_order') + 1;

        return ItemEffectType::create($validated);
    }

    public function show(ItemEffectType $itemEffectType)
    {
        return $itemEffectType;
    }

    public function update(Request $request, ItemEffectType $itemEffectType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:item_effect_types,name,' . $itemEffectType->id,
            'label' => 'sometimes|string',
            'sort_order' => 'nullable|integer',
        ]);

        $itemEffectType->update($validated);
        return $itemEffectType;
    }

    public function destroy(ItemEffectType $itemEffectType)
    {
        $itemEffectType->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
