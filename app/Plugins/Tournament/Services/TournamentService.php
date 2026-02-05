<?php

namespace App\Plugins\Tournament\Services;

use App\Plugins\Tournament\Models\Tournament;
use App\Plugins\Tournament\Models\TournamentParticipant;
use App\Plugins\Tournament\Models\TournamentMatch;
use App\Plugins\Tournament\Models\TournamentRound;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TournamentService
{
    /**
     * Get all available tournaments
     */
    public function getTournaments(array $filters = [], int $perPage = 20)
    {
        $query = Tournament::withCount('participants');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', ['upcoming', 'registration', 'in_progress']);
        }

        return $query->orderBy('start_date')->paginate($perPage);
    }

    /**
     * Get a single tournament with bracket info
     */
    public function getTournament(Tournament $tournament): array
    {
        $tournament->load(['participants.user:id,username,level', 'rounds.matches']);

        return [
            'tournament' => $tournament,
            'bracket' => $this->generateBracketView($tournament),
        ];
    }

    /**
     * Register a user for a tournament
     */
    public function register(Tournament $tournament, User $user): TournamentParticipant
    {
        if ($tournament->status !== 'registration') {
            throw new \Exception('Registration is not open for this tournament.');
        }

        if ($tournament->max_participants > 0) {
            $currentCount = $tournament->participants()->count();
            if ($currentCount >= $tournament->max_participants) {
                throw new \Exception('This tournament is full.');
            }
        }

        $existing = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            throw new \Exception('You are already registered for this tournament.');
        }

        // Check entry fee
        if ($tournament->entry_fee > 0) {
            if ($user->cash < $tournament->entry_fee) {
                throw new \Exception('Insufficient funds for entry fee.');
            }
            $user->decrement('cash', $tournament->entry_fee);
        }

        return TournamentParticipant::create([
            'tournament_id' => $tournament->id,
            'user_id' => $user->id,
            'seed' => null,
            'status' => 'registered',
        ]);
    }

    /**
     * Withdraw from a tournament
     */
    public function withdraw(Tournament $tournament, User $user): void
    {
        if (!in_array($tournament->status, ['registration', 'upcoming'])) {
            throw new \Exception('Cannot withdraw after the tournament has started.');
        }

        $participant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Refund entry fee
        if ($tournament->entry_fee > 0) {
            $user->increment('cash', $tournament->entry_fee);
        }

        $participant->delete();
    }

    /**
     * Start a tournament (admin action)
     */
    public function startTournament(Tournament $tournament): Tournament
    {
        if ($tournament->status !== 'registration') {
            throw new \Exception('Tournament must be in registration phase to start.');
        }

        $participants = $tournament->participants()->get();
        $count = $participants->count();

        if ($count < 2) {
            throw new \Exception('Need at least 2 participants to start.');
        }

        return DB::transaction(function () use ($tournament, $participants, $count) {
            // Seed participants
            $shuffled = $participants->shuffle();
            foreach ($shuffled->values() as $index => $participant) {
                $participant->update(['seed' => $index + 1]);
            }

            // Calculate rounds needed
            $rounds = (int) ceil(log($count, 2));

            // Create first round matches
            $round = TournamentRound::create([
                'tournament_id' => $tournament->id,
                'round_number' => 1,
                'name' => $this->getRoundName($rounds, 1),
            ]);

            $seeded = $shuffled->values();
            for ($i = 0; $i < $count; $i += 2) {
                TournamentMatch::create([
                    'round_id' => $round->id,
                    'tournament_id' => $tournament->id,
                    'player1_id' => $seeded[$i]->user_id,
                    'player2_id' => isset($seeded[$i + 1]) ? $seeded[$i + 1]->user_id : null,
                    'match_number' => ($i / 2) + 1,
                    'status' => isset($seeded[$i + 1]) ? 'pending' : 'bye',
                    'winner_id' => !isset($seeded[$i + 1]) ? $seeded[$i]->user_id : null,
                ]);
            }

            $tournament->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'total_rounds' => $rounds,
            ]);

            return $tournament->fresh(['rounds.matches', 'participants']);
        });
    }

    /**
     * Record a match result
     */
    public function recordMatchResult(TournamentMatch $match, int $winnerId, array $stats = []): TournamentMatch
    {
        if ($match->status !== 'pending') {
            throw new \Exception('This match is not pending.');
        }

        if (!in_array($winnerId, [$match->player1_id, $match->player2_id])) {
            throw new \Exception('Winner must be one of the match participants.');
        }

        return DB::transaction(function () use ($match, $winnerId, $stats) {
            $loserId = $winnerId === $match->player1_id ? $match->player2_id : $match->player1_id;

            $match->update([
                'winner_id' => $winnerId,
                'loser_id' => $loserId,
                'status' => 'completed',
                'stats' => $stats ?: null,
                'completed_at' => now(),
            ]);

            // Update participant status for loser
            TournamentParticipant::where('tournament_id', $match->tournament_id)
                ->where('user_id', $loserId)
                ->update(['status' => 'eliminated']);

            // Check if round is complete, advance winners
            $this->checkRoundCompletion($match);

            return $match->fresh();
        });
    }

    /**
     * Check if the current round is complete and create next round
     */
    protected function checkRoundCompletion(TournamentMatch $match): void
    {
        $round = $match->round;
        $pendingMatches = $round->matches()->where('status', 'pending')->count();

        if ($pendingMatches > 0) {
            return; // Round not done yet
        }

        $tournament = Tournament::find($match->tournament_id);
        $winners = $round->matches->pluck('winner_id')->filter()->values();

        if ($winners->count() <= 1) {
            // Tournament is over
            $this->completeTournament($tournament, $winners->first());
            return;
        }

        // Create next round
        $nextRoundNumber = $round->round_number + 1;
        $totalRounds = $tournament->total_rounds;

        $nextRound = TournamentRound::create([
            'tournament_id' => $tournament->id,
            'round_number' => $nextRoundNumber,
            'name' => $this->getRoundName($totalRounds, $nextRoundNumber),
        ]);

        for ($i = 0; $i < $winners->count(); $i += 2) {
            TournamentMatch::create([
                'round_id' => $nextRound->id,
                'tournament_id' => $tournament->id,
                'player1_id' => $winners[$i],
                'player2_id' => $winners[$i + 1] ?? null,
                'match_number' => ($i / 2) + 1,
                'status' => isset($winners[$i + 1]) ? 'pending' : 'bye',
                'winner_id' => !isset($winners[$i + 1]) ? $winners[$i] : null,
            ]);
        }
    }

    /**
     * Complete a tournament and distribute prizes
     */
    protected function completeTournament(Tournament $tournament, ?int $winnerId): void
    {
        $tournament->update([
            'status' => 'completed',
            'winner_id' => $winnerId,
            'completed_at' => now(),
        ]);

        if ($winnerId) {
            TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('user_id', $winnerId)
                ->update(['status' => 'winner']);

            // Distribute prize pool
            if ($tournament->prize_pool > 0) {
                $winner = User::find($winnerId);
                $winner?->increment('cash', $tournament->prize_pool);
            }
        }
    }

    /**
     * Generate a visual bracket structure
     */
    protected function generateBracketView(Tournament $tournament): array
    {
        $bracket = [];

        foreach ($tournament->rounds->sortBy('round_number') as $round) {
            $bracket[] = [
                'round' => $round->round_number,
                'name' => $round->name,
                'matches' => $round->matches->map(fn ($match) => [
                    'id' => $match->id,
                    'player1' => $match->player1 ? [
                        'id' => $match->player1_id,
                        'username' => $match->player1->username ?? 'TBD',
                    ] : null,
                    'player2' => $match->player2 ? [
                        'id' => $match->player2_id,
                        'username' => $match->player2->username ?? 'TBD',
                    ] : null,
                    'winner_id' => $match->winner_id,
                    'status' => $match->status,
                ])->toArray(),
            ];
        }

        return $bracket;
    }

    /**
     * Get round name based on position
     */
    protected function getRoundName(int $totalRounds, int $currentRound): string
    {
        $remaining = $totalRounds - $currentRound;
        return match ($remaining) {
            0 => 'Finals',
            1 => 'Semi-Finals',
            2 => 'Quarter-Finals',
            default => 'Round ' . $currentRound,
        };
    }
}
