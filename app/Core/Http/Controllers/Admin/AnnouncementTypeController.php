<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\AnnouncementType;
use Illuminate\Http\Request;

class AnnouncementTypeController extends Controller
{
    public function index()
    {
        return response()->json(AnnouncementType::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:announcement_types,name',
            'label' => 'required|string|max:100',
            'color' => 'nullable|string|max:200',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? AnnouncementType::max('sort_order') + 1;
        return response()->json(AnnouncementType::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(AnnouncementType::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = AnnouncementType::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:50|unique:announcement_types,name,' . $item->id,
            'label' => 'string|max:100',
            'color' => 'nullable|string|max:200',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        AnnouncementType::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
