<?php

namespace App\Plugins\Tournament\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tournament\Models\Tournament;
use App\Plugins\Tournament\Models\TournamentParticipant;
use App\Plugins\Tournament\Models\TournamentMatch;
use App\Plugins\Tournament\TournamentModule;
use Illuminate\Http\Request;

class TournamentManagementController extends Controller
{
    /**
     * List all tournaments for admin
     */
    public function index(Request $request)
    {
        $tournaments = Tournament::withCount('participants')
            ->with('winner:id,username')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'tournaments' => $tournaments,
        ]);
    }

    /**
     * Create a new tournament
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:single_elimination,double_elimination,round_robin,swiss',
            'max_participants' => 'required|integer|min:4|max:256',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'entry_fee' => 'nullable|integer|min:0',
            'prize_pool' => 'nullable|integer|min:0',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'starts_at' => 'required|date|after:registration_end',
            'rules' => 'nullable|array',
        ]);

        $validated['status'] = 'scheduled';
        $validated['current_round'] = 1;
        $validated['prize_pool'] = $validated['prize_pool'] ?? 0;
        $validated['entry_fee'] = $validated['entry_fee'] ?? 0;
        $validated['rules'] = $validated['rules'] ?? [];

        $tournament = Tournament::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tournament created',
            'tournament' => $tournament,
        ]);
    }

    /**
     * Get tournament details
     */
    public function show(int $id)
    {
        $tournament = Tournament::withCount('participants')
            ->with([
                'participants.user:id,username,level',
                'winner:id,username',
            ])
            ->find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        return response()->json([
            'success' => true,
            'tournament' => $tournament,
        ]);
    }

    /**
     * Update a tournament
     */
    public function update(Request $request, int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:single_elimination,double_elimination,round_robin,swiss',
            'max_participants' => 'sometimes|integer|min:4|max:256',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'entry_fee' => 'nullable|integer|min:0',
            'prize_pool' => 'nullable|integer|min:0',
            'registration_start' => 'sometimes|date',
            'registration_end' => 'sometimes|date',
            'starts_at' => 'sometimes|date',
            'rules' => 'nullable|array',
            'status' => 'sometimes|in:scheduled,registration,active,completed,cancelled',
        ]);

        $tournament->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated',
            'tournament' => $tournament,
        ]);
    }

    /**
     * Delete a tournament
     */
    public function destroy(int $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        if ($tournament->status === 'active') {
            return response()->json(['success' => false, 'message' => 'Cannot delete active tournament'], 400);
        }

        // Delete all related data
        TournamentMatch::where('tournament_id', $id)->delete();
        TournamentParticipant::where('tournament_id', $id)->delete();
        $tournament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tournament deleted',
        ]);
    }

    /**
     * Start a tournament
     */
    public function start(int $id)
    {
        $tournament = Tournament::with('participants.user')->find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        if ($tournament->status !== 'registration' && $tournament->status !== 'scheduled') {
            return response()->json(['success' => false, 'message' => 'Tournament cannot be started'], 400);
        }

        if ($tournament->participants->count() < 4) {
            return response()->json(['success' => false, 'message' => 'Need at least 4 participants'], 400);
        }

        // Generate bracket
        $module = new TournamentModule(app());
        $bracket = $module->generateBracket($tournament);

        $tournament->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament started',
            'bracket' => $bracket,
        ]);
    }

    /**
     * Record match result
     */
    public function recordResult(Request $request, int $tournamentId, int $matchId)
    {
        $match = TournamentMatch::where('tournament_id', $tournamentId)
            ->where('id', $matchId)
            ->first();

        if (!$match) {
            return response()->json(['success' => false, 'message' => 'Match not found'], 404);
        }

        if ($match->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Match already completed'], 400);
        }

        $validated = $request->validate([
            'winner_id' => 'required|exists:users,id',
            'player1_score' => 'nullable|integer|min:0',
            'player2_score' => 'nullable|integer|min:0',
        ]);

        if ($validated['winner_id'] != $match->player1_id && $validated['winner_id'] != $match->player2_id) {
            return response()->json(['success' => false, 'message' => 'Winner must be a participant'], 400);
        }

        $match->update([
            'winner_id' => $validated['winner_id'],
            'player1_score' => $validated['player1_score'] ?? null,
            'player2_score' => $validated['player2_score'] ?? null,
            'status' => 'completed',
            'played_at' => now(),
        ]);

        // Update participant stats
        $winnerId = $validated['winner_id'];
        $loserId = $match->player1_id == $winnerId ? $match->player2_id : $match->player1_id;

        TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('user_id', $winnerId)
            ->increment('wins');

        $loserParticipant = TournamentParticipant::where('tournament_id', $tournamentId)
            ->where('user_id', $loserId)
            ->first();

        if ($loserParticipant) {
            $loserParticipant->increment('losses');

            $tournament = Tournament::find($tournamentId);
            if ($tournament->type === 'single_elimination') {
                $loserParticipant->update(['eliminated' => true, 'final_rank' => null]);
            }
        }

        // Advance winner to next round if applicable
        $this->advanceWinner($match, $winnerId);

        return response()->json([
            'success' => true,
            'message' => 'Match result recorded',
            'match' => $match->fresh(['player1', 'player2', 'winner']),
        ]);
    }

    /**
     * Advance winner to next match
     */
    protected function advanceWinner(TournamentMatch $match, int $winnerId)
    {
        $nextMatch = TournamentMatch::where('tournament_id', $match->tournament_id)
            ->where('round', $match->round + 1)
            ->where(function ($query) use ($match) {
                // Find the next match this winner should go to
                $nextMatchNumber = intdiv($match->match_number - 1, 2) + 1;
                $query->where('match_number', $nextMatchNumber);
            })
            ->first();

        if ($nextMatch) {
            // Determine which slot (player1 or player2) based on odd/even match number
            if ($match->match_number % 2 === 1) {
                $nextMatch->update(['player1_id' => $winnerId]);
            } else {
                $nextMatch->update(['player2_id' => $winnerId]);
            }
        } else {
            // This was the final match
            $tournament = Tournament::find($match->tournament_id);
            $tournament->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'ended_at' => now(),
            ]);

            // Award prize
            if ($tournament->prize_pool > 0) {
                \App\Core\Models\User::find($winnerId)->increment('money', $tournament->prize_pool);
            }

            // Set final rank for winner
            TournamentParticipant::where('tournament_id', $match->tournament_id)
                ->where('user_id', $winnerId)
                ->update(['final_rank' => 1]);
        }
    }

    /**
     * Cancel a tournament
     */
    public function cancel(int $id)
    {
        $tournament = Tournament::with('participants')->find($id);

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'Tournament not found'], 404);
        }

        if ($tournament->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Cannot cancel completed tournament'], 400);
        }

        // Refund entry fees
        if ($tournament->entry_fee > 0) {
            foreach ($tournament->participants as $participant) {
                \App\Core\Models\User::find($participant->user_id)->increment('money', $tournament->entry_fee);
            }
        }

        $tournament->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Tournament cancelled and entry fees refunded',
        ]);
    }
}
