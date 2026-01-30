<?php

namespace App\Modules\Racing;

use App\Modules\Module;
use App\Models\User;
use App\Models\Race;

/**
 * Racing Module
 * 
 * Handles street racing competitions between players
 * Requires vehicles from inventory system
 */
class RacingModule extends Module
{
    protected string $name = 'Racing';
    
    public function construct(): void
    {
        $this->config = [
            'min_entry_fee' => 100,
            'max_entry_fee' => 1000000,
            'min_participants' => 2,
            'max_participants' => 8,
            'refund_percentage' => 0.90, // 90% refund on leave
        ];
    }
    
    /**
     * Get available races
     */
    public function getAvailableRaces(User $player): array
    {
        return Race::where('status', 'waiting')
            ->where('started_at', null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($race) {
                return $this->applyModuleHook('alterRaceData', [
                    'race' => $race,
                    'creator' => $race->creator,
                    'participants' => $race->participants,
                    'entry_fee' => $race->entry_fee,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Get race history for player
     */
    public function getRaceHistory(User $player, int $limit = 10): array
    {
        return $player->races()
            ->where('status', 'finished')
            ->orderBy('finished_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($race) {
                return $this->applyModuleHook('alterRaceHistoryData', [
                    'race' => $race,
                    'winner' => $race->winner,
                    'prize_pool' => $race->prize_pool,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Calculate race results
     */
    public function calculateResults(Race $race): array
    {
        $participants = $race->participants;
        $results = [];
        
        foreach ($participants as $participant) {
            $vehicle = $participant->vehicle;
            $speed = $vehicle ? $vehicle->item->speed : 50;
            $luck = mt_rand(1, 100);
            
            $score = $speed + $luck;
            
            $results[] = [
                'participant' => $participant,
                'score' => $score,
            ];
        }
        
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return $this->applyModuleHook('alterRaceResults', $results);
    }
    
    /**
     * Register module hooks
     */
    public function registerHooks(): void
    {
        // Will be called from hooks.php
    }
}
