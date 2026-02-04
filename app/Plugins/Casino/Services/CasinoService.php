<?php

namespace App\Plugins\Casino\Services;

use App\Plugins\Casino\Models\CasinoBet;
use App\Plugins\Casino\Models\CasinoGame;
use App\Plugins\Casino\Models\Lottery;
use App\Plugins\Casino\Models\LotteryTicket;
use App\Core\Models\User;
use App\Plugins\Casino\Models\UserCasinoStats;
use Illuminate\Support\Facades\DB;

class CasinoService
{
    public function getAllGames()
    {
        return CasinoGame::where('is_active', true)->get();
    }

    public function playSlots(User $user, int $gameId, int $betAmount)
    {
        $game = CasinoGame::where('type', 'slots')->findOrFail($gameId);
        
        $this->validateBet($user, $game, $betAmount);

        $rules = $game->rules;
        $symbols = $rules['symbols'];
        $payouts = $rules['payouts'];

        // Spin the reels (3 reels)
        $reel1 = $symbols[array_rand($symbols)];
        $reel2 = $symbols[array_rand($symbols)];
        $reel3 = $symbols[array_rand($symbols)];
        
        $result = "$reel1$reel2$reel3";
        
        // Check for win
        $payout = 0;
        $isWin = false;
        
        if (isset($payouts[$result])) {
            $payout = $betAmount * $payouts[$result];
            $isWin = true;
        }

        return $this->processBet($user, $game, $betAmount, $payout, $isWin, [
            'reels' => [$reel1, $reel2, $reel3],
            'result' => $result,
        ]);
    }

    public function playRouletteNumber(User $user, int $gameId, int $betAmount, int $number)
    {
        $game = CasinoGame::where('type', 'roulette')->findOrFail($gameId);
        
        $this->validateBet($user, $game, $betAmount);

        if ($number < 0 || $number > 36) {
            throw new \Exception('Invalid number. Choose 0-36.');
        }

        $spinResult = rand(0, 36);
        $isWin = $spinResult === $number;
        $payout = $isWin ? $betAmount * 35 : 0;

        return $this->processBet($user, $game, $betAmount, $payout, $isWin, [
            'number' => $number,
            'spin_result' => $spinResult,
        ]);
    }

    public function playRouletteColor(User $user, int $gameId, int $betAmount, string $color)
    {
        $game = CasinoGame::where('type', 'roulette')->findOrFail($gameId);
        
        $this->validateBet($user, $game, $betAmount);

        if (!in_array($color, ['red', 'black'])) {
            throw new \Exception('Invalid color. Choose red or black.');
        }

        $spinResult = rand(0, 36);
        
        // 0 is green (no win)
        if ($spinResult === 0) {
            $isWin = false;
        } else {
            $redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
            $spinColor = in_array($spinResult, $redNumbers) ? 'red' : 'black';
            $isWin = $spinColor === $color;
        }

        $payout = $isWin ? $betAmount * 2 : 0;

        return $this->processBet($user, $game, $betAmount, $payout, $isWin, [
            'color' => $color,
            'spin_result' => $spinResult,
        ]);
    }

    public function playDice(User $user, int $gameId, int $betAmount, string $choice)
    {
        $game = CasinoGame::where('type', 'dice')->findOrFail($gameId);
        
        $this->validateBet($user, $game, $betAmount);

        if (!in_array($choice, ['high', 'low'])) {
            throw new \Exception('Invalid choice. Choose high or low.');
        }

        $die1 = rand(1, 6);
        $die2 = rand(1, 6);
        $total = $die1 + $die2;

        $isWin = false;
        if ($choice === 'low' && $total >= 2 && $total <= 6) {
            $isWin = true;
        } elseif ($choice === 'high' && $total >= 8 && $total <= 12) {
            $isWin = true;
        }

        $payout = $isWin ? $betAmount * 2 : 0;

        return $this->processBet($user, $game, $betAmount, $payout, $isWin, [
            'choice' => $choice,
            'die1' => $die1,
            'die2' => $die2,
            'total' => $total,
        ]);
    }

    protected function validateBet(User $user, CasinoGame $game, int $betAmount)
    {
        if ($betAmount < $game->min_bet) {
            throw new \Exception("Minimum bet is {$game->min_bet}.");
        }

        if ($betAmount > $game->max_bet) {
            throw new \Exception("Maximum bet is {$game->max_bet}.");
        }

        if ($user->cash < $betAmount) {
            throw new \Exception('Insufficient funds.');
        }
    }

    protected function processBet(User $user, CasinoGame $game, int $betAmount, int $payout, bool $isWin, array $gameData)
    {
        $profitLoss = $payout - $betAmount;
        $result = $isWin ? ($payout === $betAmount ? 'push' : 'win') : 'loss';

        DB::beginTransaction();
        try {
            // Deduct bet amount
            $user->decrement('cash', $betAmount);

            // Add winnings if any
            if ($payout > 0) {
                $user->increment('cash', $payout);
            }

            // Record bet
            CasinoBet::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'bet_amount' => $betAmount,
                'payout' => $payout,
                'profit_loss' => $profitLoss,
                'result' => $result,
                'game_data' => $gameData,
                'played_at' => now(),
            ]);

            // Update user stats
            $stats = UserCasinoStats::firstOrCreate(['user_id' => $user->id]);
            
            $stats->increment('total_bets');
            $stats->increment('total_wagered', $betAmount);
            
            if ($isWin) {
                $stats->increment('total_won', $profitLoss);
                $stats->increment('current_streak');
                $stats->biggest_win = max($stats->biggest_win, $profitLoss);
                $stats->best_streak = max($stats->best_streak, $stats->current_streak);
            } else {
                $stats->increment('total_lost', abs($profitLoss));
                $stats->current_streak = 0;
                $stats->biggest_loss = max($stats->biggest_loss, abs($profitLoss));
            }
            
            $stats->net_profit = $stats->total_won - $stats->total_lost;
            $stats->save();

            DB::commit();

            return [
                'result' => $result,
                'bet_amount' => $betAmount,
                'payout' => $payout,
                'profit_loss' => $profitLoss,
                'new_balance' => $user->fresh()->cash,
                'game_data' => $gameData,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUserStats(User $user)
    {
        return UserCasinoStats::firstOrCreate(['user_id' => $user->id]);
    }

    public function getBetHistory(User $user, int $limit = 20)
    {
        return CasinoBet::where('user_id', $user->id)
            ->with('game')
            ->orderBy('played_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function buyLotteryTicket(User $user, int $lotteryId, array $numbers)
    {
        $lottery = Lottery::where('status', 'active')->findOrFail($lotteryId);

        if (count($numbers) !== 6) {
            throw new \Exception('Please select 6 numbers.');
        }

        if ($user->cash < $lottery->ticket_price) {
            throw new \Exception('Insufficient funds.');
        }

        DB::beginTransaction();
        try {
            $user->decrement('cash', $lottery->ticket_price);
            $lottery->increment('prize_pool', $lottery->ticket_price);

            $ticket = LotteryTicket::create([
                'lottery_id' => $lottery->id,
                'user_id' => $user->id,
                'numbers' => $numbers,
                'purchased_at' => now(),
            ]);

            DB::commit();

            return $ticket;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
