<?php

namespace App\Plugins\OrganizedCrime\Services;

use App\Plugins\OrganizedCrimes\Models\OrganizedCrime;
use App\Core\Models\User;
use App\Plugins\Gang\Models\Gang;
use Illuminate\Support\Facades\DB;

class OrganizedCrimeService
{
    public function attemptOrganizedCrime(User $leader, OrganizedCrime $crime, array $participantIds): array
    {
        // Validate leader has a gang
        if (!$leader->gang_id) {
            throw new \Exception('You must be in a gang to attempt organized crimes.');
        }

        $gang = Gang::find($leader->gang_id);
        
        if ($gang->leader_id !== $leader->id) {
            throw new \Exception('Only the gang leader can initiate organized crimes.');
        }

        // Validate participants
        $participants = User::whereIn('id', $participantIds)
            ->where('gang_id', $gang->id)
            ->get();

        if ($participants->count() < $crime->required_members) {
            throw new \Exception('Need ' . $crime->required_members . ' gang members to attempt this crime.');
        }

        // Check level requirement
        foreach ($participants as $participant) {
            if ($participant->level < $crime->required_level) {
                throw new \Exception($participant->username . ' needs to be level ' . $crime->required_level);
            }
        }

        // Calculate success
        $baseChance = $crime->success_rate;
        $levelBonus = $participants->avg('level') * 2;
        $finalChance = min(95, $baseChance + $levelBonus);
        
        $success = mt_rand(1, 100) <= $finalChance;

        return DB::transaction(function () use ($crime, $gang, $leader, $participants, $success) {
            if ($success) {
                $reward = mt_rand($crime->min_reward, $crime->max_reward);
                $rewardPerMember = floor($reward / $participants->count());

                // Distribute rewards
                foreach ($participants as $participant) {
                    $participant->increment('cash', $rewardPerMember);
                    $participant->increment('respect', 50);
                }

                $message = 'Success! Your gang earned $' . number_format($reward) . ' ($' . number_format($rewardPerMember) . ' each)';
            } else {
                // Jail some members on failure
                $jailedCount = mt_rand(1, min(3, $participants->count()));
                $jailed = $participants->random($jailedCount);
                
                foreach ($jailed as $prisoner) {
                    $prisoner->update(['jail_until' => now()->addSeconds($crime->cooldown / 2)]);
                }

                $reward = 0;
                $message = 'Failed! ' . $jailedCount . ' members were arrested.';
            }

            // Log attempt
            DB::table('organized_crime_attempts')->insert([
                'organized_crime_id' => $crime->id,
                'gang_id' => $gang->id,
                'leader_id' => $leader->id,
                'success' => $success,
                'reward_earned' => $reward ?? 0,
                'participants' => json_encode($participants->pluck('id')->toArray()),
                'result_message' => $message,
                'attempted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => $success,
                'message' => $message,
                'reward' => $reward ?? 0,
            ];
        });
    }

    public function getAvailableCrimes()
    {
        return OrganizedCrime::orderBy('required_level')
            ->orderBy('required_members')
            ->get();
    }

    public function getGangHistory(Gang $gang)
    {
        return DB::table('organized_crime_attempts')
            ->where('gang_id', $gang->id)
            ->join('organized_crimes', 'organized_crime_attempts.organized_crime_id', '=', 'organized_crimes.id')
            ->select('organized_crimes.name', 'organized_crime_attempts.*')
            ->orderByDesc('attempted_at')
            ->limit(20)
            ->get();
    }
}
