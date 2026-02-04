<?php

namespace App\Plugins\Tournament\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tournament\Models\Tournament;
use App\Plugins\Tournament\Models\TournamentParticipant;
use App\Plugins\Tournament\Models\TournamentMatch;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    /**
     * List tournaments
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'active');

        $query = Tournament::withCount('participants');

        switch ($filter) {
            case 'upcoming':
                $query->where('status', 'scheduled');
                break;
            case 'completed':
                $query->where('status', 'completed')->with('winner:id,username');
                break;
            case 'registration':
                $query->registrationOpen();
                break;
            default:
                $query->active();
        }

        $tournaments = $query->orderBy('starts_at')->paginate(15);

        return response()->json([
            'success' => true,
            'tournaments' => $tournaments,
        ]);
    }

    /**
     * Get tournament details
     */
    public function show(int $id)
    {
        $tournament = Tournament::with([
            'participants.user:id,username,level,avatar',
            'winner:id,username',
        ])->withCount('participants')->find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $userParticipation = null;
        if (auth()->check()) {
            $userParticipation = TournamentParticipant::where('tournament_id', $id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return response()->json([
            'success' => true,
            'tournament' => $tournament,
            'participation' => $userParticipation,
        ]);
    }

    /**
     * Get tournament bracket
     */
    public function bracket(int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $matches = TournamentMatch::where('tournament_id', $id)
            ->with(['player1:id,username', 'player2:id,username', 'winner:id,username'])
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        // Group by round
        $bracket = [];
        foreach ($matches as $match) {
            $bracket[$match->round][] = $match;
        }

        return response()->json([
            'success' => true,
            'bracket' => $bracket,
            'current_round' => $tournament->getCurrentRound(),
        ]);
    }

    /**
     * Register for tournament
     */
    public function register(int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        if (!$tournament->isRegistrationOpen()) {
            return response()->json(['success' => false, 'message' => 'Registration is closed'], 400);
        }

        $user = auth()->user();

        if ($tournament->min_level && $user->level < $tournament->min_level) {
            return response()->json(['success' => false, 'message' => 'Level too low'], 400);
        }

        if ($tournament->max_level && $user->level > $tournament->max_level) {
            return response()->json(['success' => false, 'message' => 'Level too high'], 400);
        }

        $existing = TournamentParticipant::where('tournament_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already registered'], 400);
        }

        if ($tournament->entry_fee > 0) {
            if ($user->money < $tournament->entry_fee) {
                return response()->json(['success' => false, 'message' => 'Not enough money for entry fee'], 400);
            }
            $user->decrement('money', $tournament->entry_fee);
            $tournament->increment('prize_pool', $tournament->entry_fee);
        }

        TournamentParticipant::create([
            'tournament_id' => $id,
            'user_id' => $user->id,
            'registered_at' => now(),
            'wins' => 0,
            'losses' => 0,
            'points' => 0,
            'eliminated' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registered for tournament',
        ]);
    }

    /**
     * Withdraw from tournament
     */
    public function withdraw(int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        if ($tournament->status !== 'registration') {
            return response()->json(['success' => false, 'message' => 'Cannot withdraw after tournament starts'], 400);
        }

        $participant = TournamentParticipant::where('tournament_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Not registered'], 404);
        }

        // Refund entry fee
        if ($tournament->entry_fee > 0) {
            auth()->user()->increment('money', $tournament->entry_fee);
            $tournament->decrement('prize_pool', $tournament->entry_fee);
        }

        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Withdrawn from tournament',
        ]);
    }

    /**
     * Get match details
     */
    public function match(int $tournamentId, int $matchId)
    {
        $match = TournamentMatch::where('tournament_id', $tournamentId)
            ->where('id', $matchId)
            ->with(['player1:id,username,level', 'player2:id,username,level', 'winner:id,username'])
            ->first();

        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        return response()->json([
            'success' => true,
            'match' => $match,
        ]);
    }

    /**
     * Leaderboard for tournament
     */
    public function leaderboard(int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $leaderboard = TournamentParticipant::where('tournament_id', $id)
            ->with('user:id,username,level,avatar')
            ->orderBy('eliminated')
            ->orderBy('wins', 'desc')
            ->orderBy('points', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
        ]);
    }
}
