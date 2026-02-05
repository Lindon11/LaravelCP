<?php

namespace App\Plugins\OrganizedCrime;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Gang\Models\Gang;
use App\Plugins\OrganizedCrime\Models\OrganizedCrime;

/**
 * Organized Crime Module
 *
 * Handles organized crime system - coordinate gang crimes for big rewards
 * Requires gang membership and multiple participants
 */
class OrganizedCrimeModule extends Plugin
{
    protected string $name = 'OrganizedCrime';

    public function construct(): void
    {
        $this->config = [
            'cooldown' => 3600, // 1 hour
            'min_participants' => 3,
            'max_participants' => 10,
            'failure_jail_time' => 600, // 10 minutes
        ];
    }

    /**
     * Get available organized crimes for gang
     */
    public function getAvailableCrimes(Gang $gang): array
    {
        return OrganizedCrime::where('enabled', true)
            ->where('required_members', '<=', $gang->getMemberCount())
            ->orderBy('difficulty')
            ->get()
            ->map(function ($crime) use ($gang) {
                return $this->applyModuleHook('alterOrganizedCrimeData', [
                    'crime' => $crime,
                    'gang' => $gang,
                    'success_rate' => $this->calculateSuccessRate($crime, $gang),
                ]);
            })
            ->toArray();
    }

    /**
     * Attempt an organized crime
     */
    public function attemptCrime(User $user, OrganizedCrime $crime, array $participantIds): array
    {
        // Check gang membership
        if (!$user->gang_id) {
            $this->error('You must be in a gang to attempt organized crimes');
            return ['success' => false, 'message' => 'You must be in a gang to attempt organized crimes'];
        }

        $gang = Gang::find($user->gang_id);

        // Check cooldown
        if ($gang->hasTimer('organized_crime')) {
            $this->error('Gang must wait before attempting another organized crime');
            return ['success' => false, 'message' => 'Gang must wait before attempting another organized crime'];
        }

        // Validate participants
        if (count($participantIds) < $crime->required_members) {
            $this->error("This crime requires at least {$crime->required_members} participants");
            return ['success' => false, 'message' => "This crime requires at least {$crime->required_members} participants"];
        }

        // Apply hooks before attempt
        $this->applyModuleHook('beforeOrganizedCrimeAttempt', [
            'user' => $user,
            'gang' => $gang,
            'crime' => $crime,
            'participants' => $participantIds,
        ]);

        // Calculate success
        $successRate = $this->calculateSuccessRate($crime, $gang);
        $success = (rand(1, 100) / 100) <= $successRate;

        $result = [];

        if ($success) {
            $totalCash = rand($crime->min_cash, $crime->max_cash);
            $cashPerPerson = floor($totalCash / count($participantIds));
            $expPerPerson = $crime->experience;

            // Distribute rewards
            foreach ($participantIds as $participantId) {
                $participant = User::find($participantId);
                if ($participant) {
                    $participant->cash += $cashPerPerson;
                    $participant->experience += $expPerPerson;
                    $participant->save();
                }
            }

            // Add gang respect
            $gang->respect += $crime->respect_reward;
            $gang->save();

            $this->success("Organized crime successful! Each member earned {$this->money($cashPerPerson)} and {$expPerPerson} EXP. Gang gained {$crime->respect_reward} respect!");
            $result = [
                'success' => true,
                'cash_earned' => $cashPerPerson,
                'exp_earned' => $expPerPerson,
                'respect_gained' => $crime->respect_reward,
            ];
        } else {
            // All participants go to jail
            foreach ($participantIds as $participantId) {
                $participant = User::find($participantId);
                if ($participant) {
                    $participant->jail_until = now()->addSeconds($this->config['failure_jail_time']);
                    $participant->save();
                }
            }

            $this->error("Organized crime failed! All participants were caught and sent to jail!");
            $result = [
                'success' => false,
                'jailed' => true,
            ];
        }

        // Set cooldown
        $gang->setTimer('organized_crime', $this->config['cooldown']);

        // Track action
        $this->trackAction('organized_crime_attempt', [
            'crime_id' => $crime->id,
            'gang_id' => $gang->id,
            'success' => $success,
        ]);

        // Fire hook after attempt
        $this->applyModuleHook('afterOrganizedCrimeAttempt', [
            'user' => $user,
            'gang' => $gang,
            'crime' => $crime,
            'success' => $success,
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Calculate success rate based on gang stats
     */
    protected function calculateSuccessRate(OrganizedCrime $crime, Gang $gang): float
    {
        $baseRate = $crime->success_rate / 100;

        // Adjust based on gang level
        $levelBonus = $gang->level * 0.02;

        // Adjust based on member count
        $memberBonus = $gang->getMemberCount() * 0.01;

        $finalRate = $baseRate + $levelBonus + $memberBonus;

        // Apply module hooks
        $finalRate = $this->applyModuleHook('modifyOrganizedCrimeSuccessRate', [
            'base_rate' => $baseRate,
            'final_rate' => $finalRate,
            'gang' => $gang,
            'crime' => $crime,
        ])['final_rate'] ?? $finalRate;

        return min(0.90, max(0.10, $finalRate));
    }
}
