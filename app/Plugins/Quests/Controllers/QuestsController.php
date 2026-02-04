<?php

namespace App\Plugins\Quests\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Quests\Models\Quest;
use App\Plugins\Quests\Models\PlayerQuest;
use Illuminate\Http\Request;

class QuestsController extends Controller
{
    /**
     * List available quests
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $category = $request->get('category');

        $completedQuestIds = PlayerQuest::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('quest_id')
            ->toArray();

        $activeQuestIds = PlayerQuest::where('user_id', $user->id)
            ->active()
            ->pluck('quest_id')
            ->toArray();

        $query = Quest::enabled()
            ->where('min_level', '<=', $user->level)
            ->where(function ($q) use ($user) {
                $q->whereNull('max_level')
                    ->orWhere('max_level', '>=', $user->level);
            })
            ->orderBy('sort_order');

        if ($category) {
            $query->category($category);
        }

        $quests = $query->get()->map(function ($quest) use ($completedQuestIds, $activeQuestIds) {
            $quest->is_completed = in_array($quest->id, $completedQuestIds);
            $quest->is_active = in_array($quest->id, $activeQuestIds);
            $quest->is_available = !$quest->is_active &&
                (!$quest->is_completed || $quest->repeatable) &&
                (!$quest->prerequisite_quest_id || in_array($quest->prerequisite_quest_id, $completedQuestIds));
            return $quest;
        });

        return response()->json([
            'success' => true,
            'quests' => $quests,
        ]);
    }

    /**
     * Get active quests
     */
    public function active()
    {
        $quests = PlayerQuest::where('user_id', auth()->id())
            ->active()
            ->with('quest')
            ->get();

        return response()->json([
            'success' => true,
            'quests' => $quests,
        ]);
    }

    /**
     * Get completed quests
     */
    public function completed()
    {
        $quests = PlayerQuest::where('user_id', auth()->id())
            ->completed()
            ->with('quest')
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'quests' => $quests,
        ]);
    }

    /**
     * Get quest details
     */
    public function show(int $id)
    {
        $quest = Quest::find($id);

        if (!$quest) {
            return response()->json(['success' => false, 'message' => 'Quest not found'], 404);
        }

        $playerQuest = PlayerQuest::where('user_id', auth()->id())
            ->where('quest_id', $id)
            ->whereIn('status', ['active', 'in_progress', 'ready_to_complete'])
            ->first();

        return response()->json([
            'success' => true,
            'quest' => $quest,
            'player_progress' => $playerQuest,
        ]);
    }

    /**
     * Start a quest
     */
    public function start(int $id)
    {
        $user = auth()->user();
        $quest = Quest::find($id);

        if (!$quest || !$quest->enabled) {
            return response()->json(['success' => false, 'message' => 'Quest not available'], 404);
        }

        if (!$quest->isAvailableForLevel($user->level)) {
            return response()->json(['success' => false, 'message' => 'Quest not available for your level'], 400);
        }

        // Check prerequisite
        if ($quest->prerequisite_quest_id) {
            $prereqComplete = PlayerQuest::where('user_id', $user->id)
                ->where('quest_id', $quest->prerequisite_quest_id)
                ->where('status', 'completed')
                ->exists();

            if (!$prereqComplete) {
                return response()->json(['success' => false, 'message' => 'Prerequisite quest not completed'], 400);
            }
        }

        // Check if already active
        $existing = PlayerQuest::where('user_id', $user->id)
            ->where('quest_id', $id)
            ->active()
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Quest already active'], 400);
        }

        // Check max active quests
        $activeCount = PlayerQuest::where('user_id', $user->id)->active()->count();
        if ($activeCount >= 5) {
            return response()->json(['success' => false, 'message' => 'Maximum active quests reached'], 400);
        }

        $playerQuest = PlayerQuest::create([
            'user_id' => $user->id,
            'quest_id' => $id,
            'status' => 'active',
            'started_at' => now(),
            'progress' => [],
            'expires_at' => $quest->time_limit ? now()->addSeconds($quest->time_limit) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quest started: ' . $quest->name,
            'quest' => $playerQuest->load('quest'),
        ]);
    }

    /**
     * Abandon a quest
     */
    public function abandon(int $id)
    {
        $playerQuest = PlayerQuest::where('user_id', auth()->id())
            ->where('quest_id', $id)
            ->active()
            ->first();

        if (!$playerQuest) {
            return response()->json(['success' => false, 'message' => 'Quest not found'], 404);
        }

        $playerQuest->update(['status' => 'abandoned']);

        return response()->json([
            'success' => true,
            'message' => 'Quest abandoned',
        ]);
    }

    /**
     * Complete a quest and claim rewards
     */
    public function complete(int $id)
    {
        $user = auth()->user();
        $playerQuest = PlayerQuest::where('user_id', $user->id)
            ->where('quest_id', $id)
            ->whereIn('status', ['active', 'in_progress', 'ready_to_complete'])
            ->with('quest')
            ->first();

        if (!$playerQuest) {
            return response()->json(['success' => false, 'message' => 'Quest not found'], 404);
        }

        if (!$playerQuest->areObjectivesComplete()) {
            return response()->json(['success' => false, 'message' => 'Quest objectives not complete'], 400);
        }

        $quest = $playerQuest->quest;
        $rewards = $quest->rewards ?? [];

        // Apply rewards
        if (isset($rewards['money'])) {
            $user->increment('money', $rewards['money']);
        }
        if (isset($rewards['experience'])) {
            $user->increment('experience', $rewards['experience']);
        }

        $playerQuest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'reward_claimed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quest completed: ' . $quest->name,
            'rewards' => $rewards,
        ]);
    }
}
