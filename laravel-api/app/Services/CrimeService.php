<?php

namespace App\Services;

use App\Models\Crime;
use App\Models\Rank;
use App\Models\User;
use App\Models\CrimeAttempt;
use Carbon\Carbon;

class CrimeService
{
    protected $timerService;
    protected $rankProgressionService;
    protected $notificationService;
    
    public function __construct(
        TimerService $timerService, 
        RankProgressionService $rankProgressionService,
        NotificationService $notificationService
    ) {
        $this->timerService = $timerService;
        $this->rankProgressionService = $rankProgressionService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Attempt to commit a crime - uses legacy formula from old system
     */
    public function attemptCrime(User $player, Crime $crime): array
    {
        // Check if player has an active crime cooldown
        if ($this->timerService->hasActiveTimer($player, 'crime')) {
            $remaining = $this->timerService->getRemainingSeconds($player, 'crime');
            return [
                'success' => false,
                'message' => 'You must wait before committing another crime!',
                'cooldown' => $remaining
            ];
        }
        
        // Check if player is in jail
        if ($player->jail_until && $player->jail_until->isFuture()) {
            return [
                'success' => false,
                'message' => 'You are in jail!',
                'jail_time' => $player->jail_until->diffInSeconds(now())
            ];
        }

        // Check level requirement
        if ($player->level < $crime->required_level) {
            return [
                'success' => false,
                'message' => "You need to be level {$crime->required_level} to commit this crime!"
            ];
        }

        // Check energy requirement
        if ($player->energy < $crime->energy_cost) {
            return [
                'success' => false,
                'message' => "You don't have enough energy! Need {$crime->energy_cost}, you have {$player->energy}."
            ];
        }

        // Deduct energy
        $player->energy -= $crime->energy_cost;

        // Legacy formula: Random chance vs success rate
        $chance = mt_rand(1, 100);
        $jailChance = mt_rand(1, 3);
        
        // Calculate rewards
        $cashReward = mt_rand($crime->min_cash, $crime->max_cash);
        
        // LEGACY FORMULA: Failed and caught (sent to jail)
        if ($chance > $crime->success_rate && $jailChance == 1) {
            $jailTime = $crime->id * 15; // 15 seconds per crime level
            
            $player->update([
                'jail_until' => now()->addSeconds($jailTime),
                'last_crime_at' => now()
            ]);

            CrimeAttempt::create([
                'user_id' => $player->id,
                'crime_id' => $crime->id,
                'success' => false,
                'cash_earned' => 0,
                'respect_earned' => 0,
                'result_message' => 'Caught by police and sent to jail!',
                'attempted_at' => now()
            ]);

            return [
                'success' => false,
                'jailed' => true,
                'message' => "You failed to commit the crime and were caught! Sent to jail for {$jailTime} seconds.",
                'jail_time' => $jailTime
            ];
        }
        
        // LEGACY FORMULA: Failed but escaped
        if ($chance > $crime->success_rate) {
            $player->update([
                'last_crime_at' => now()
            ]);

            CrimeAttempt::create([
                'user_id' => $player->id,
                'crime_id' => $crime->id,
                'success' => false,
                'cash_earned' => 0,
                'respect_earned' => 0,
                'result_message' => 'Failed but escaped',
                'attempted_at' => now()
            ]);

            return [
                'success' => false,
                'message' => 'You failed to commit the crime but managed to escape!'
            ];
        }

        // LEGACY FORMULA: Success!
        $oldLevel = $player->level;
        
        $player->cash += $cashReward;
        $player->respect += $crime->respect_reward;
        $player->last_crime_at = now();
        
        // Add experience and handle level-ups using RankProgressionService
        $progressResult = $this->rankProgressionService->addExperience(
            $player, 
            $crime->experience_reward, 
            "crime:{$crime->name}"
        );
        
        $player->save();
        
        // Set crime cooldown (5 seconds)
        $this->timerService->setTimer($player, 'crime', 5, [
            'crime_id' => $crime->id,
            'crime_name' => $crime->name
        ]);

        CrimeAttempt::create([
            'user_id' => $player->id,
            'crime_id' => $crime->id,
            'success' => true,
            'cash_earned' => $cashReward,
            'respect_earned' => $crime->respect_reward,
            'result_message' => 'Successfully completed!',
            'attempted_at' => now()
        ]);

        $message = "You successfully committed {$crime->name}!";
        $rewards = [
            'cash' => $cashReward,
            'respect' => $crime->respect_reward,
            'experience' => $crime->experience_reward,
            'progress' => $progressResult,
        ];
        
        if ($progressResult['levels_gained'] > 0) {
            $message .= " Level up! You are now level {$player->level} ({$player->rank})!";
            $rewards['leveled_up'] = true;
            $rewards['new_level'] = $player->level;
        }

        return [
            'success' => true,
            'message' => $message,
            'rewards' => $rewards
        ];
    }

    /**
     * Get available crimes for player
     */
    public function getAvailableCrimes(User $player): array
    {
        return Crime::where('active', true)
            ->where('required_level', '<=', $player->level)
            ->orderBy('required_level')
            ->get()
            ->toArray();
    }
}
