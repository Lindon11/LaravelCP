<?php

namespace App\Plugins\Casino\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Casino\Models\Lottery;
use App\Plugins\Casino\Models\LotteryTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LotteryController extends Controller
{
    /**
     * Display a listing of lotteries
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lottery::withCount('tickets');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lotteries = $query->orderBy('draw_date', 'desc')->paginate(20);

        return response()->json($lotteries);
    }

    /**
     * Store a newly created lottery
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ticket_price' => 'required|numeric|min:0',
            'max_tickets' => 'nullable|integer|min:1',
            'prize_pool' => 'required|numeric|min:0',
            'draw_date' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lottery = Lottery::create(array_merge($request->all(), [
            'status' => 'active',
        ]));

        return response()->json($lottery, 201);
    }

    /**
     * Display the specified lottery
     */
    public function show(int $id): JsonResponse
    {
        $lottery = Lottery::withCount('tickets')
            ->with(['winner', 'tickets' => function($query) {
                $query->with('user')->orderBy('purchased_at', 'desc')->limit(20);
            }])
            ->findOrFail($id);

        return response()->json($lottery);
    }

    /**
     * Update the specified lottery
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $lottery = Lottery::findOrFail($id);

        // Cannot update completed lotteries
        if ($lottery->status === 'completed') {
            return response()->json([
                'error' => 'Cannot update completed lottery'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'ticket_price' => 'sometimes|required|numeric|min:0',
            'max_tickets' => 'nullable|integer|min:1',
            'prize_pool' => 'sometimes|required|numeric|min:0',
            'draw_date' => 'sometimes|required|date|after:now',
            'status' => 'sometimes|in:active,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lottery->update($request->all());

        return response()->json($lottery);
    }

    /**
     * Remove the specified lottery
     */
    public function destroy(int $id): JsonResponse
    {
        $lottery = Lottery::findOrFail($id);
        
        // Cannot delete completed lotteries
        if ($lottery->status === 'completed') {
            return response()->json([
                'error' => 'Cannot delete completed lottery'
            ], 400);
        }

        // Refund all tickets if lottery is cancelled
        if ($lottery->tickets()->exists()) {
            DB::transaction(function () use ($lottery) {
                foreach ($lottery->tickets as $ticket) {
                    $ticket->user->increment('cash', $lottery->ticket_price);
                }
                $lottery->delete();
            });

            return response()->json(['message' => 'Lottery deleted and all tickets refunded']);
        }

        $lottery->delete();

        return response()->json(['message' => 'Lottery deleted successfully']);
    }

    /**
     * Draw a winner for the lottery
     */
    public function drawWinner(int $id): JsonResponse
    {
        $lottery = Lottery::with('tickets.user')->findOrFail($id);

        // Validate lottery status
        if ($lottery->status !== 'active') {
            return response()->json([
                'error' => 'Lottery must be active to draw a winner'
            ], 400);
        }

        // Check if lottery has tickets
        if ($lottery->tickets()->count() === 0) {
            return response()->json([
                'error' => 'Lottery has no tickets'
            ], 400);
        }

        DB::transaction(function () use ($lottery) {
            // Pick random winner
            $winningTicket = $lottery->tickets()->inRandomOrder()->first();
            
            // Update lottery
            $lottery->update([
                'winner_id' => $winningTicket->user_id,
                'winning_ticket_id' => $winningTicket->id,
                'status' => 'completed',
            ]);

            // Award prize to winner
            $winningTicket->user->increment('cash', $lottery->prize_pool);
        });

        return response()->json([
            'message' => 'Winner drawn successfully',
            'lottery' => $lottery->fresh(['winner', 'tickets']),
        ]);
    }
}
