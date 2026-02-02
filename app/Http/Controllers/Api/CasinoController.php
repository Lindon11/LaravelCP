<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CasinoService;
use Illuminate\Http\Request;

class CasinoController extends Controller
{
    protected $casinoService;

    public function __construct(CasinoService $casinoService)
    {
        $this->casinoService = $casinoService;
    }

    public function games()
    {
        $games = $this->casinoService->getAllGames();
        return response()->json(['games' => $games]);
    }

    public function playSlots(Request $request)
    {
        $request->validate(['game_id' => 'required|exists:casino_games,id', 'bet_amount' => 'required|integer|min:1']);
        try {
            $result = $this->casinoService->playSlots($request->user(), $request->game_id, $request->bet_amount);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function playRoulette(Request $request)
    {
        $request->validate(['game_id' => 'required|exists:casino_games,id', 'bet_amount' => 'required|integer|min:1', 'bet_type' => 'required|in:number,color', 'bet_value' => 'required']);
        try {
            if ($request->bet_type === 'number') {
                $result = $this->casinoService->playRouletteNumber($request->user(), $request->game_id, $request->bet_amount, (int)$request->bet_value);
            } else {
                $result = $this->casinoService->playRouletteColor($request->user(), $request->game_id, $request->bet_amount, $request->bet_value);
            }
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function playDice(Request $request)
    {
        $request->validate(['game_id' => 'required|exists:casino_games,id', 'bet_amount' => 'required|integer|min:1', 'choice' => 'required|in:high,low']);
        try {
            $result = $this->casinoService->playDice($request->user(), $request->game_id, $request->bet_amount, $request->choice);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function stats(Request $request)
    {
        $stats = $this->casinoService->getUserStats($request->user());
        return response()->json(['stats' => $stats]);
    }

    public function history(Request $request)
    {
        $history = $this->casinoService->getBetHistory($request->user());
        return response()->json(['history' => $history]);
    }

    public function buyLotteryTicket(Request $request)
    {
        $request->validate(['lottery_id' => 'required|exists:lotteries,id', 'numbers' => 'required|array|size:6', 'numbers.*' => 'integer|min:1|max:49']);
        try {
            $ticket = $this->casinoService->buyLotteryTicket($request->user(), $request->lottery_id, $request->numbers);
            return response()->json(['message' => 'Lottery ticket purchased!', 'ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
