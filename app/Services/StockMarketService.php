<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockPriceHistory;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\UserStock;
use Illuminate\Support\Facades\DB;

class StockMarketService
{
    public function getAllStocks()
    {
        return Stock::where('is_active', true)->get();
    }

    public function getStockDetails(int $stockId)
    {
        return Stock::with(['priceHistory' => function ($query) {
            $query->orderBy('recorded_at', 'desc')->limit(30);
        }])->findOrFail($stockId);
    }

    public function getUserPortfolio(User $user)
    {
        return UserStock::where('user_id', $user->id)
            ->with('stock')
            ->where('shares', '>', 0)
            ->get()
            ->map(function ($userStock) {
                $stock = $userStock->stock;
                $userStock->current_value = $userStock->shares * $stock->current_price;
                $userStock->profit_loss = $userStock->current_value - $userStock->total_invested;
                $userStock->profit_loss_percentage = $userStock->total_invested > 0 
                    ? (($userStock->profit_loss / $userStock->total_invested) * 100) 
                    : 0;
                return $userStock;
            });
    }

    public function buyStock(User $user, int $stockId, int $shares)
    {
        if ($shares <= 0) {
            throw new \Exception('Invalid number of shares.');
        }

        $stock = Stock::findOrFail($stockId);

        if ($shares > $stock->shares_available) {
            throw new \Exception('Not enough shares available.');
        }

        $totalCost = $shares * $stock->current_price;

        if ($user->cash < $totalCost) {
            throw new \Exception('Insufficient funds.');
        }

        DB::beginTransaction();
        try {
            // Deduct cash from user
            $user->decrement('cash', $totalCost);

            // Update stock availability
            $stock->decrement('shares_available', $shares);
            $stock->increment('shares_traded', $shares);

            // Record transaction
            StockTransaction::create([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
                'type' => 'buy',
                'shares' => $shares,
                'price_per_share' => $stock->current_price,
                'total_amount' => $totalCost,
                'fees' => 0,
                'executed_at' => now(),
            ]);

            // Update user portfolio
            $userStock = UserStock::firstOrNew([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
            ]);

            $totalShares = $userStock->shares + $shares;
            $totalInvested = $userStock->total_invested + $totalCost;
            
            $userStock->shares = $totalShares;
            $userStock->total_invested = $totalInvested;
            $userStock->average_buy_price = $totalInvested / $totalShares;
            $userStock->current_value = $totalShares * $stock->current_price;
            $userStock->profit_loss = $userStock->current_value - $totalInvested;
            $userStock->save();

            DB::commit();

            return [
                'shares_purchased' => $shares,
                'price_per_share' => $stock->current_price,
                'total_cost' => $totalCost,
                'new_balance' => $user->fresh()->cash,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function sellStock(User $user, int $stockId, int $shares)
    {
        if ($shares <= 0) {
            throw new \Exception('Invalid number of shares.');
        }

        $userStock = UserStock::where('user_id', $user->id)
            ->where('stock_id', $stockId)
            ->first();

        if (!$userStock || $userStock->shares < $shares) {
            throw new \Exception('You do not own enough shares.');
        }

        $stock = Stock::findOrFail($stockId);
        $totalRevenue = $shares * $stock->current_price;

        DB::beginTransaction();
        try {
            // Add cash to user
            $user->increment('cash', $totalRevenue);

            // Update stock availability
            $stock->increment('shares_available', $shares);

            // Record transaction
            StockTransaction::create([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
                'type' => 'sell',
                'shares' => $shares,
                'price_per_share' => $stock->current_price,
                'total_amount' => $totalRevenue,
                'fees' => 0,
                'executed_at' => now(),
            ]);

            // Update user portfolio
            $remainingShares = $userStock->shares - $shares;
            
            if ($remainingShares > 0) {
                $costOfSoldShares = ($shares / $userStock->shares) * $userStock->total_invested;
                
                $userStock->shares = $remainingShares;
                $userStock->total_invested -= $costOfSoldShares;
                $userStock->current_value = $remainingShares * $stock->current_price;
                $userStock->profit_loss = $userStock->current_value - $userStock->total_invested;
                $userStock->save();
            } else {
                $userStock->delete();
            }

            DB::commit();

            return [
                'shares_sold' => $shares,
                'price_per_share' => $stock->current_price,
                'total_revenue' => $totalRevenue,
                'new_balance' => $user->fresh()->cash,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStockPrices()
    {
        $stocks = Stock::where('is_active', true)->get();

        foreach ($stocks as $stock) {
            // Calculate price change based on volatility
            $changePercent = (rand(-100, 100) / 100) * ($stock->volatility / 100);
            $priceChange = $stock->current_price * ($changePercent / 100);
            $newPrice = max(1, $stock->current_price + $priceChange);

            $stock->update([
                'current_price' => $newPrice,
                'day_high' => max($stock->day_high, $newPrice),
                'day_low' => min($stock->day_low, $newPrice),
                'market_cap' => $newPrice * ($stock->shares_available + $stock->shares_traded),
            ]);

            // Record price history
            StockPriceHistory::create([
                'stock_id' => $stock->id,
                'price' => $newPrice,
                'open_price' => $stock->day_open,
                'close_price' => $newPrice,
                'high_price' => $stock->day_high,
                'low_price' => $stock->day_low,
                'volume' => $stock->shares_traded,
                'recorded_at' => now(),
            ]);
        }
    }
}
