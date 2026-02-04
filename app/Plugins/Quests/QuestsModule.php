<?php

namespace App\Plugins\Quests;

use App\Plugins\Plugin;
use App\Plugins\Quests\Models\Quest;
use App\Plugins\Quests\Models\PlayerQuest;

/**
 * Quests Module
 *
 * Story-driven quest chains with objectives and rewards
 */
class QuestsModule extends Plugin
{
    protected string $name = 'Quests';

    public function construct(): void
    {
        $this->config = [
            'max_active_quests' => 5,
            'quest_categories' => [
                'main' => 'Main Story',
                'side' => 'Side Quest',
                'daily' => 'Daily Quest',
                'weekly' => 'Weekly Quest',
                'tutorial' => 'Tutorial',
            ],
        ];
    }

    /**
     * Get available quests for a player
     */
    public function getAvailableQuests(int $userId): array
    {
        $completedQuestIds = PlayerQuest::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('quest_id')
            ->toArray();

        $activeQuestIds = PlayerQuest::where('user_id', $userId)
            ->whereIn('status', ['active', 'in_progress'])
            ->pluck('quest_id')
            ->toArray();

        return Quest::where('enabled', true)
            ->whereNotIn('id', $activeQuestIds)
            ->where(function ($q) use ($completedQuestIds) {
                $q->whereNull('prerequisite_quest_id')
                    ->orWhereIn('prerequisite_quest_id', $completedQuestIds);
            })
            ->where(function ($q) use ($completedQuestIds) {
                $q->where('repeatable', true)
                    ->orWhereNotIn('id', $completedQuestIds);
            })
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * Get player's active quests
     */
    public function getActiveQuests(int $userId): array
    {
        return PlayerQuest::where('user_id', $userId)
            ->whereIn('status', ['active', 'in_progress'])
            ->with(['quest', 'objectives'])
            ->get()
            ->toArray();
    }

    /**
     * Get completed quests
     */
    public function getCompletedQuests(int $userId): array
    {
        return PlayerQuest::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('quest')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Start a quest
     */
    public function startQuest(int $userId, int $questId): array
    {
        $quest = Quest::find($questId);

        if (!$quest || !$quest->enabled) {
            return ['success' => false, 'message' => 'Quest not available'];
        }

        $activeCount = PlayerQuest::where('user_id', $userId)
            ->whereIn('status', ['active', 'in_progress'])
            ->count();

        if ($activeCount >= $this->config['max_active_quests']) {
            return ['success' => false, 'message' => 'Maximum active quests reached'];
        }

        $existing = PlayerQuest::where('user_id', $userId)
            ->where('quest_id', $questId)
            ->whereIn('status', ['active', 'in_progress'])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Quest already active'];
        }

        $playerQuest = PlayerQuest::create([
            'user_id' => $userId,
            'quest_id' => $questId,
            'status' => 'active',
            'started_at' => now(),
            'progress' => [],
        ]);

        return ['success' => true, 'message' => 'Quest started', 'data' => $playerQuest];
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'total_quests' => Quest::where('enabled', true)->count(),
                'active' => 0,
                'completed' => 0,
            ];
        }

        return [
            'total_quests' => Quest::where('enabled', true)->count(),
            'active' => PlayerQuest::where('user_id', $user->id)
                ->whereIn('status', ['active', 'in_progress'])
                ->count(),
            'completed' => PlayerQuest::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
        ];
    }
}
