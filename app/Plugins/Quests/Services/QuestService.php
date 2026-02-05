<?php

namespace App\Plugins\Quests\Services;

use App\Plugins\Quests\Models\Quest;
use App\Plugins\Quests\Models\QuestProgress;
use App\Plugins\Quests\Models\QuestObjective;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class QuestService
{
    /**
     * Get all available quests for a user
     */
    public function getAvailableQuests(User $user, int $perPage = 20)
    {
        return Quest::where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('min_level', '<=', $user->level ?? 1)
                    ->orWhereNull('min_level');
            })
            ->with('objectives')
            ->withCount(['progress as completed_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->paginate($perPage);
    }

    /**
     * Get user's active quests
     */
    public function getActiveQuests(User $user)
    {
        return QuestProgress::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['quest.objectives', 'objectiveProgress'])
            ->get();
    }

    /**
     * Get user's completed quests
     */
    public function getCompletedQuests(User $user, int $perPage = 20)
    {
        return QuestProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('quest')
            ->orderByDesc('completed_at')
            ->paginate($perPage);
    }

    /**
     * Accept a quest
     */
    public function acceptQuest(Quest $quest, User $user): QuestProgress
    {
        if (!$quest->is_active) {
            throw new \Exception('This quest is not available.');
        }

        $existing = QuestProgress::where('user_id', $user->id)
            ->where('quest_id', $quest->id)
            ->whereIn('status', ['in_progress', 'completed'])
            ->first();

        if ($existing) {
            $status = $existing->status === 'completed' ? 'already completed' : 'already in progress';
            throw new \Exception("This quest is {$status}.");
        }

        // Check prerequisites
        if ($quest->prerequisite_quest_id) {
            $completed = QuestProgress::where('user_id', $user->id)
                ->where('quest_id', $quest->prerequisite_quest_id)
                ->where('status', 'completed')
                ->exists();

            if (!$completed) {
                throw new \Exception('You have not completed the prerequisite quest.');
            }
        }

        return DB::transaction(function () use ($quest, $user) {
            $progress = QuestProgress::create([
                'user_id' => $user->id,
                'quest_id' => $quest->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Initialize objective progress
            foreach ($quest->objectives as $objective) {
                DB::table('quest_objective_progress')->insert([
                    'quest_progress_id' => $progress->id,
                    'objective_id' => $objective->id,
                    'current_count' => 0,
                    'target_count' => $objective->target_count,
                    'completed' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $progress->load(['quest.objectives', 'objectiveProgress']);
        });
    }

    /**
     * Abandon a quest
     */
    public function abandonQuest(QuestProgress $progress, User $user): void
    {
        if ($progress->user_id !== $user->id) {
            throw new \Exception('This is not your quest.');
        }

        if ($progress->status !== 'in_progress') {
            throw new \Exception('Can only abandon quests in progress.');
        }

        $progress->update(['status' => 'abandoned']);
    }

    /**
     * Update quest objective progress (called by game hooks)
     */
    public function updateObjectiveProgress(User $user, string $objectiveType, int $amount = 1): void
    {
        $activeQuests = QuestProgress::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with('quest.objectives')
            ->get();

        foreach ($activeQuests as $progress) {
            foreach ($progress->quest->objectives as $objective) {
                if ($objective->type === $objectiveType) {
                    DB::table('quest_objective_progress')
                        ->where('quest_progress_id', $progress->id)
                        ->where('objective_id', $objective->id)
                        ->where('completed', false)
                        ->increment('current_count', $amount);

                    // Check if objective now completed
                    $objProgress = DB::table('quest_objective_progress')
                        ->where('quest_progress_id', $progress->id)
                        ->where('objective_id', $objective->id)
                        ->first();

                    if ($objProgress && $objProgress->current_count >= $objProgress->target_count) {
                        DB::table('quest_objective_progress')
                            ->where('quest_progress_id', $progress->id)
                            ->where('objective_id', $objective->id)
                            ->update(['completed' => true, 'updated_at' => now()]);
                    }
                }
            }

            // Check if all objectives are completed
            $this->checkQuestCompletion($progress);
        }
    }

    /**
     * Check if all objectives for a quest are completed
     */
    public function checkQuestCompletion(QuestProgress $progress): bool
    {
        $total = DB::table('quest_objective_progress')
            ->where('quest_progress_id', $progress->id)
            ->count();

        $completed = DB::table('quest_objective_progress')
            ->where('quest_progress_id', $progress->id)
            ->where('completed', true)
            ->count();

        if ($total > 0 && $completed === $total) {
            $progress->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Claim quest rewards
     */
    public function claimRewards(QuestProgress $progress, User $user): array
    {
        if ($progress->user_id !== $user->id) {
            throw new \Exception('This is not your quest.');
        }

        if ($progress->status !== 'completed') {
            throw new \Exception('Quest is not completed yet.');
        }

        if ($progress->rewards_claimed) {
            throw new \Exception('Rewards already claimed.');
        }

        $quest = $progress->quest;
        $rewards = [];

        if ($quest->reward_cash > 0) {
            $user->increment('cash', $quest->reward_cash);
            $rewards['cash'] = $quest->reward_cash;
        }

        if ($quest->reward_exp > 0) {
            $user->increment('exp', $quest->reward_exp);
            $rewards['exp'] = $quest->reward_exp;
        }

        if ($quest->reward_items) {
            $rewards['items'] = $quest->reward_items;
            // Item granting handled by inventory service hooks
        }

        $progress->update(['rewards_claimed' => true]);

        return $rewards;
    }
}
