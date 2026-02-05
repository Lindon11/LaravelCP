<?php

namespace App\Plugins\Alliances\Services;

use App\Plugins\Alliances\Models\Alliance;
use App\Plugins\Alliances\Models\AllianceMember;
use App\Plugins\Alliances\Models\AllianceWar;
use App\Plugins\Alliances\Models\Territory;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class AllianceService
{
    /**
     * Get all alliances with member/territory counts
     */
    public function getAlliances(int $perPage = 20)
    {
        return Alliance::withCount(['members', 'territories'])
            ->orderByDesc('level')
            ->paginate($perPage);
    }

    /**
     * Get alliance details with members and wars
     */
    public function getAlliance(int $id): array
    {
        $alliance = Alliance::with(['members.user', 'territories'])
            ->withCount(['members', 'territories'])
            ->findOrFail($id);

        $activeWars = AllianceWar::where(function ($q) use ($id) {
            $q->where('attacker_id', $id)->orWhere('defender_id', $id);
        })->where('status', 'active')
            ->with(['attacker', 'defender'])
            ->get();

        return [
            'alliance' => $alliance,
            'active_wars' => $activeWars,
        ];
    }

    /**
     * Create a new alliance
     */
    public function createAlliance(User $user, array $data): Alliance
    {
        return DB::transaction(function () use ($user, $data) {
            $cost = config('plugins.Alliances.creation_cost', 50000);

            if ($user->cash < $cost) {
                throw new \Exception('Insufficient funds to create an alliance.');
            }

            $user->decrement('cash', $cost);

            $alliance = Alliance::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'leader_id' => $user->id,
                'level' => 1,
                'treasury' => 0,
            ]);

            AllianceMember::create([
                'alliance_id' => $alliance->id,
                'user_id' => $user->id,
                'role' => 'leader',
                'joined_at' => now(),
            ]);

            return $alliance;
        });
    }

    /**
     * Add a member to an alliance
     */
    public function addMember(Alliance $alliance, User $user, string $role = 'member'): AllianceMember
    {
        $maxMembers = 10 + ($alliance->level * 5);

        if ($alliance->members()->count() >= $maxMembers) {
            throw new \Exception("Alliance is full ({$maxMembers} max).");
        }

        if (AllianceMember::where('user_id', $user->id)->exists()) {
            throw new \Exception('Player is already in an alliance.');
        }

        return AllianceMember::create([
            'alliance_id' => $alliance->id,
            'user_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from an alliance
     */
    public function removeMember(Alliance $alliance, User $user): void
    {
        $member = AllianceMember::where('alliance_id', $alliance->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($member->role === 'leader') {
            throw new \Exception('Alliance leader cannot leave. Transfer leadership first.');
        }

        $member->delete();
    }

    /**
     * Declare war on another alliance
     */
    public function declareWar(Alliance $attacker, Alliance $defender, int $warCost = 10000): AllianceWar
    {
        if ($attacker->id === $defender->id) {
            throw new \Exception('Cannot declare war on your own alliance.');
        }

        $existingWar = AllianceWar::where('status', 'active')
            ->where(function ($q) use ($attacker, $defender) {
                $q->where(function ($q2) use ($attacker, $defender) {
                    $q2->where('attacker_id', $attacker->id)->where('defender_id', $defender->id);
                })->orWhere(function ($q2) use ($attacker, $defender) {
                    $q2->where('attacker_id', $defender->id)->where('defender_id', $attacker->id);
                });
            })->first();

        if ($existingWar) {
            throw new \Exception('There is already an active war between these alliances.');
        }

        if ($attacker->treasury < $warCost) {
            throw new \Exception('Insufficient alliance treasury to declare war.');
        }

        $attacker->decrement('treasury', $warCost);

        return AllianceWar::create([
            'attacker_id' => $attacker->id,
            'defender_id' => $defender->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    /**
     * Get territories, optionally filtered by alliance
     */
    public function getTerritories(?int $allianceId = null)
    {
        $query = Territory::with(['alliance']);

        if ($allianceId) {
            $query->where('alliance_id', $allianceId);
        }

        return $query->get();
    }

    /**
     * Deposit funds into alliance treasury
     */
    public function deposit(Alliance $alliance, User $user, int $amount): void
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be positive.');
        }

        if ($user->cash < $amount) {
            throw new \Exception('Insufficient funds.');
        }

        DB::transaction(function () use ($alliance, $user, $amount) {
            $user->decrement('cash', $amount);
            $alliance->increment('treasury', $amount);
        });
    }
}
