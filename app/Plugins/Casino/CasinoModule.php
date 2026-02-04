<?php

namespace App\Plugins\Casino;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Casino\Models\CasinoGame;
use App\Plugins\Casino\Models\CasinoBet;
use App\Plugins\Casino\Models\Lottery;
use App\Plugins\Casino\Models\LotteryTicket;
use App\Plugins\Casino\Models\UserCasinoStats;
use Illuminate\Support\Facades\DB;

/**
 * Casino Module
 * 
 * Handles casino games: slots, roulette, dice, and lottery
 */
class CasinoModule extends Plugin
{
    protected string $name = 'Casino';
    
    public function construct(): void
    {
        $this->config = [
            'min_bet' => 100,
            'max_bet' => 1000000,
            'house_edge' => 0.05, // 5% house edge
        ];
    }
    
    /**
     * Get all available casino games
     */
    public function getAllGames()
    {
        return CasinoGame::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
    
    /**
     * Play slots
     */
    public function playSlots(User $user, int $gameId, int $betAmount): array
    {
        $game = CasinoGame::findOrFail($gameId);
        
        $this->validateBet($user, $betAmount, $game);
        
        // Deduct bet
        $user->cash -= $betAmount;
        
        // Generate slot result
        $symbols = ['🍒', '🍋', '🔔', '⭐', '💎', '7️⃣'];
        $reels = [
            $symbols[array_rand($symbols)],
            $symbols[array_rand($symbols)],
            $symbols[array_rand($symbols)]
        ];
        
        // Calculate win
        $winAmount = 0;
        $isWin = false;
        
        if ($reels[0] === $reels[1] && $reels[1] === $reels[2]) {
            // Jackpot - 3 matching
            $multiplier = match($reels[0]) {
                '💎' => 50,
                '7️⃣' => 100,
                '⭐' => 25,
                default => 10
            };
            $winAmount = $betAmount * $multiplier;
            $isWin = true;
        } elseif ($reels[0] === $reels[1] || $reels[1] === $reels[2]) {
            // 2 matching
            $winAmount = $betAmount * 2;
            $isWin = true;
        }
        
        // Apply hooks
        if ($isWin) {
            $hookResult = $this->applyModuleHook('alterWinnings', [
                'amount' => $winAmount,
                'user' => $user,
                'game' => 'slots',
            ]);
            $winAmount = $hookResult['amount'] ?? $winAmount;
            $user->cash += $winAmount;
            
            $this->applyModuleHook('OnBetWon', [
                'user' => $user,
                'game' => 'slots',
                'bet' => $betAmount,
                'win' => $winAmount,
            ]);
        } else {
            $this->applyModuleHook('OnBetLost', [
                'user' => $user,
                'game' => 'slots',
                'bet' => $betAmount,
            ]);
        }
        
        $user->save();
        
        // Record bet
        $this->recordBet($user, $game, $betAmount, $winAmount, $isWin, ['reels' => $reels]);
        
        return [
            'success' => true,
            'reels' => $reels,
            'win' => $isWin,
            'win_amount' => $winAmount,
            'new_balance' => $user->cash,
        ];
    }
    
    /**
     * Play roulette (number)
     */
    public function playRouletteNumber(User $user, int $gameId, int $betAmount, int $number): array
    {
        $game = CasinoGame::findOrFail($gameId);
        $this->validateBet($user, $betAmount, $game);
        
        if ($number < 0 || $number > 36) {
            throw new \Exception('Invalid number. Must be 0-36.');
        }
        
        $user->cash -= $betAmount;
        
        $result = rand(0, 36);
        $isWin = $result === $number;
        $winAmount = $isWin ? $betAmount * 35 : 0;
        
        if ($isWin) {
            $hookResult = $this->applyModuleHook('alterWinnings', [
                'amount' => $winAmount,
                'user' => $user,
                'game' => 'roulette',
            ]);
            $winAmount = $hookResult['amount'] ?? $winAmount;
            $user->cash += $winAmount;
        }
        
        $user->save();
        $this->recordBet($user, $game, $betAmount, $winAmount, $isWin, ['bet_number' => $number, 'result' => $result]);
        
        return [
            'success' => true,
            'result' => $result,
            'bet_number' => $number,
            'win' => $isWin,
            'win_amount' => $winAmount,
            'new_balance' => $user->cash,
        ];
    }
    
    /**
     * Play roulette (color)
     */
    public function playRouletteColor(User $user, int $gameId, int $betAmount, string $color): array
    {
        $game = CasinoGame::findOrFail($gameId);
        $this->validateBet($user, $betAmount, $game);
        
        $user->cash -= $betAmount;
        
        $result = rand(0, 36);
        $redNumbers = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
        $resultColor = $result === 0 ? 'green' : (in_array($result, $redNumbers) ? 'red' : 'black');
        
        $isWin = $resultColor === $color;
        $winAmount = $isWin ? $betAmount * 2 : 0;
        
        if ($isWin) {
            $user->cash += $winAmount;
        }
        
        $user->save();
        $this->recordBet($user, $game, $betAmount, $winAmount, $isWin, ['bet_color' => $color, 'result' => $result, 'result_color' => $resultColor]);
        
        return [
            'success' => true,
            'result' => $result,
            'result_color' => $resultColor,
            'bet_color' => $color,
            'win' => $isWin,
            'win_amount' => $winAmount,
            'new_balance' => $user->cash,
        ];
    }
    
    /**
     * Play dice
     */
    public function playDice(User $user, int $gameId, int $betAmount, string $choice): array
    {
        $game = CasinoGame::findOrFail($gameId);
        $this->validateBet($user, $betAmount, $game);
        
        $user->cash -= $betAmount;
        
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
        $total = $dice1 + $dice2;
        
        $resultChoice = $total >= 7 ? 'high' : 'low';
        $isWin = $choice === $resultChoice;
        $winAmount = $isWin ? (int)($betAmount * 1.9) : 0;
        
        if ($isWin) {
            $user->cash += $winAmount;
        }
        
        $user->save();
        $this->recordBet($user, $game, $betAmount, $winAmount, $isWin, ['dice' => [$dice1, $dice2], 'choice' => $choice]);
        
        return [
            'success' => true,
            'dice' => [$dice1, $dice2],
            'total' => $total,
            'choice' => $choice,
            'result' => $resultChoice,
            'win' => $isWin,
            'win_amount' => $winAmount,
            'new_balance' => $user->cash,
        ];
    }
    
    /**
     * Buy lottery ticket
     */
    public function buyLotteryTicket(User $user, int $lotteryId, array $numbers): LotteryTicket
    {
        $lottery = Lottery::findOrFail($lotteryId);
        
        if ($user->cash < $lottery->ticket_price) {
            throw new \Exception('Not enough cash to buy lottery ticket.');
        }
        
        $user->cash -= $lottery->ticket_price;
        $user->save();
        
        $ticket = LotteryTicket::create([
            'lottery_id' => $lotteryId,
            'user_id' => $user->id,
            'numbers' => $numbers,
        ]);
        
        $this->applyModuleHook('OnLotteryTicketPurchased', [
            'user' => $user,
            'lottery' => $lottery,
            'ticket' => $ticket,
        ]);
        
        return $ticket;
    }
    
    /**
     * Get user casino stats
     */
    public function getUserStats(User $user): array
    {
        $stats = UserCasinoStats::firstOrCreate(['user_id' => $user->id]);
        
        return [
            'total_bets' => $stats->total_bets ?? 0,
            'total_wagered' => $stats->total_wagered ?? 0,
            'total_won' => $stats->total_won ?? 0,
            'total_lost' => $stats->total_lost ?? 0,
            'biggest_win' => $stats->biggest_win ?? 0,
            'net_profit' => ($stats->total_won ?? 0) - ($stats->total_lost ?? 0),
        ];
    }
    
    /**
     * Get bet history
     */
    public function getBetHistory(User $user, int $limit = 50)
    {
        return CasinoBet::where('user_id', $user->id)
            ->with('game')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Validate bet
     */
    protected function validateBet(User $user, int $amount, CasinoGame $game): void
    {
        if ($amount < ($game->min_bet ?? $this->config['min_bet'])) {
            throw new \Exception('Bet amount is below minimum.');
        }
        
        if ($amount > ($game->max_bet ?? $this->config['max_bet'])) {
            throw new \Exception('Bet amount exceeds maximum.');
        }
        
        if ($user->cash < $amount) {
            throw new \Exception('Not enough cash to place bet.');
        }
    }
    
    /**
     * Record a bet
     */
    protected function recordBet(User $user, CasinoGame $game, int $betAmount, int $winAmount, bool $isWin, array $details = []): void
    {
        CasinoBet::create([
            'user_id' => $user->id,
            'casino_game_id' => $game->id,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'is_win' => $isWin,
            'details' => $details,
        ]);
        
        // Update stats
        $stats = UserCasinoStats::firstOrCreate(['user_id' => $user->id]);
        $stats->total_bets = ($stats->total_bets ?? 0) + 1;
        $stats->total_wagered = ($stats->total_wagered ?? 0) + $betAmount;
        
        if ($isWin) {
            $stats->total_won = ($stats->total_won ?? 0) + $winAmount;
            if ($winAmount > ($stats->biggest_win ?? 0)) {
                $stats->biggest_win = $winAmount;
            }
        } else {
            $stats->total_lost = ($stats->total_lost ?? 0) + $betAmount;
        }
        
        $stats->save();
    }
}
