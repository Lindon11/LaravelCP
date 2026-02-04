<?php

namespace App\Plugins\Quests\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Quests\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestManagementController extends Controller
{
    /**
     * List all quests
     */
    public function index(Request $request)
    {
        $quests = Quest::orderBy('sort_order')
            ->orderBy('category')
            ->paginate(30);

        return response()->json([
            'success' => true,
            'quests' => $quests,
        ]);
    }

    /**
     * Create a quest
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'story_text' => 'nullable|string',
            'category' => 'required|string|in:main,side,daily,weekly,tutorial',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer',
            'prerequisite_quest_id' => 'nullable|exists:quests,id',
            'objectives' => 'required|array|min:1',
            'rewards' => 'required|array',
            'time_limit' => 'nullable|integer',
            'repeatable' => 'nullable|boolean',
            'repeat_cooldown' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['enabled'] = $validated['enabled'] ?? true;

        $quest = Quest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quest created',
            'quest' => $quest,
        ]);
    }

    /**
     * Update a quest
     */
    public function update(Request $request, int $id)
    {
        $quest = Quest::find($id);

        if (!$quest) {
            return response()->json(['success' => false, 'message' => 'Quest not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'story_text' => 'nullable|string',
            'category' => 'sometimes|string|in:main,side,daily,weekly,tutorial',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer',
            'prerequisite_quest_id' => 'nullable|exists:quests,id',
            'objectives' => 'sometimes|array',
            'rewards' => 'sometimes|array',
            'time_limit' => 'nullable|integer',
            'repeatable' => 'nullable|boolean',
            'repeat_cooldown' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
        ]);

        $quest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quest updated',
            'quest' => $quest->fresh(),
        ]);
    }

    /**
     * Delete a quest
     */
    public function destroy(int $id)
    {
        $quest = Quest::find($id);

        if (!$quest) {
            return response()->json(['success' => false, 'message' => 'Quest not found'], 404);
        }

        $quest->playerQuests()->delete();
        $quest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quest deleted',
        ]);
    }
}
