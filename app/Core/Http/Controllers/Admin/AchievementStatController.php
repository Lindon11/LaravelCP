<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\AchievementStat;
use Illuminate\Http\Request;

class AchievementStatController extends Controller
{
    public function index()
    {
        return response()->json(AchievementStat::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:achievement_stats,name',
            'label' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? AchievementStat::max('sort_order') + 1;
        return response()->json(AchievementStat::create($validated), 201);
    }

    public function show(string $id)
    {
        return response()->json(AchievementStat::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $item = AchievementStat::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:50|unique:achievement_stats,name,' . $item->id,
            'label' => 'string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $item->update($validated);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        AchievementStat::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
