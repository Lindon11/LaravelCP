<?php

namespace App\Plugins\Tournament;

use App\Plugins\Plugin;
use App\Plugins\Tournament\Models\Tournament;
use App\Plugins\Tournament\Models\TournamentParticipant;
use App\Plugins\Tournament\Models\TournamentMatch;

/**
 * Tournament Module
 *
 * Competitive brackets, elimination tournaments, and battle royale
 */
class TournamentModule extends Plugin
{
    protected string $name = 'Tournament';

    public function construct(): void
    {
        $this->config = [
            'types' => [
                'single_elimination' => 'Single Elimination',
                'double_elimination' => 'Double Elimination',
                'round_robin' => 'Round Robin',
                'battle_royale' => 'Battle Royale',
                'swiss' => 'Swiss System',
            ],
            'max_participants' => 128,
            'min_participants' => 4,
            'match_timeout' => 300, // 5 minutes
        ];
    }

    /**
     * Get active tournaments
     */
    public function getActiveTournaments(): array
    {
        return Tournament::whereIn('status', ['registration', 'active'])
            ->withCount('participants')
            ->orderBy('starts_at')
            ->get()
            ->toArray();
    }

    /**
     * Get upcoming tournaments
     */
    public function getUpcomingTournaments(): array
    {
        return Tournament::where('status', 'scheduled')
            ->where('registration_opens', '>', now())
            ->withCount('participants')
            ->orderBy('starts_at')
            ->get()
            ->toArray();
    }

    /**
     * Get completed tournaments
     */
    public function getCompletedTournaments(int $limit = 10): array
    {
        return Tournament::where('status', 'completed')
            ->with('winner:id,username')
            ->orderBy('ended_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Generate bracket for single elimination
     */
    public function generateSingleEliminationBracket(Tournament $tournament): array
    {
        $participants = $tournament->participants()
            ->inRandomOrder()
            ->get()
            ->pluck('user_id')
            ->toArray();

        $count = count($participants);
        $rounds = ceil(log($count, 2));
        $bracket = [];

        // First round matches
        $matchNum = 1;
        for ($i = 0; $i < $count; $i += 2) {
            $player1 = $participants[$i] ?? null;
            $player2 = $participants[$i + 1] ?? null;

            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round' => 1,
                'match_number' => $matchNum,
                'player1_id' => $player1,
                'player2_id' => $player2,
                'status' => $player2 ? 'pending' : 'bye',
                'winner_id' => $player2 ? null : $player1, // Bye advances
            ]);

            $bracket[] = $match;
            $matchNum++;
        }

        return $bracket;
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        $active = Tournament::whereIn('status', ['registration', 'active'])->count();
        $participated = 0;
        $wins = 0;

        if ($user) {
            $participated = TournamentParticipant::where('user_id', $user->id)->count();
            $wins = Tournament::where('winner_id', $user->id)->count();
        }

        return [
            'active_tournaments' => $active,
            'participated' => $participated,
            'tournament_wins' => $wins,
        ];
    }
}
