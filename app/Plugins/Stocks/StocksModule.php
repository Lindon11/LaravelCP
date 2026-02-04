<?php

namespace App\Plugins\Stocks;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Stocks\Models\Stock;
use App\Plugins\Stocks\Models\StockPriceHistory;
use App\Plugins\Stocks\Models\StockTransaction;
use App\Plugins\Stocks\Models\UserStock;
use Illuminate\Support\Facades\DB;

/**
 * Stocks Module
 * 
 * Handles stock market trading system
 */
class StocksModule extends Plugin
{
    protected string $name = 'Stocks';
    
    public function construct(): void
    {
        $this->config = [
            'default_fee_percentage' => 0.01, // 1% transaction fee
            'max_shares_per_transaction' => 10000,
        ];
    }
    
    /**
     * Get all active stocks
     */
    public function getAllStocks()
    {
        return Stock::where('is_active', true)->get();
    }
    
    /**
     * Get stock details with price history
     */
    public function getStockDetails(int $stockId)
    {
        return Stock::with(['priceHistory' => function ($query) {
            $query->orderBy('recorded_at', 'desc')->limit(30);
        }])->findOrFail($stockId);
    }
    
    /**
     * Get user's portfolio
     */
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
    
    /**
     * Buy stock shares
     */
    public function buyStock(User $user, int $stockId, int $shares): array
    {
        if ($shares <= 0) {
            throw new \Exception('Invalid number of shares.');
        }
        
        if ($shares > $this->config['max_shares_per_transaction']) {
            throw new \Exception('Maximum ' . $this->config['max_shares_per_transaction'] . ' shares per transaction.');
        }

        $stock = Stock::findOrFail($stockId);

        if ($shares > $stock->shares_available) {
            throw new \Exception('Not enough shares available.');
        }

        $totalCost = $shares * $stock->current_price;
        
        // Calculate fees with hook
        $feeData = $this->applyModuleHook('alterTransactionFees', [
            'base_fee' => $totalCost * $this->config['default_fee_percentage'],
            'user' => $user,
            'type' => 'buy',
        ]);
        $fees = $feeData['base_fee'] ?? 0;
        $totalWithFees = $totalCost + $fees;

        if ($user->cash < $totalWithFees) {
            throw new \Exception('Insufficient funds.');
        }

        DB::beginTransaction();
        try {
            $user->decrement('cash', $totalWithFees);
            $stock->decrement('shares_available', $shares);
            $stock->increment('shares_traded', $shares);

            StockTransaction::create([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
                'type' => 'buy',
                'shares' => $shares,
                'price_per_share' => $stock->current_price,
                'total_amount' => $totalCost,
                'fees' => $fees,
                'executed_at' => now(),
            ]);

            $userStock = UserStock::firstOrNew([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
            ]);

            $totalShares = ($userStock->shares ?? 0) + $shares;
            $totalInvested = ($userStock->total_invested ?? 0) + $totalCost;
            
            $userStock->shares = $totalShares;
            $userStock->total_invested = $totalInvested;
            $userStock->average_buy_price = $totalInvested / $totalShares;
            $userStock->current_value = $totalShares * $stock->current_price;
            $userStock->profit_loss = $userStock->current_value - $totalInvested;
            $userStock->save();

            DB::commit();
            
            $this->applyModuleHook('OnStockBuy', [
                'user' => $user,
                'stock' => $stock,
                'shares' => $shares,
                'total_cost' => $totalCost,
            ]);

            return [
                'shares_purchased' => $shares,
                'price_per_share' => $stock->current_price,
                'total_cost' => $totalCost,
                'fees' => $fees,
                'new_balance' => $user->fresh()->cash,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Sell stock shares
     */
    public function sellStock(User $user, int $stockId, int $shares): array
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
        
        // Calculate fees
        $feeData = $this->applyModuleHook('alterTransactionFees', [
            'base_fee' => $totalRevenue * $this->config['default_fee_percentage'],
            'user' => $user,
            'type' => 'sell',
        ]);
        $fees = $feeData['base_fee'] ?? 0;
        $netRevenue = $totalRevenue - $fees;

        DB::beginTransaction();
        try {
            $user->increment('cash', $netRevenue);
            $stock->increment('shares_available', $shares);

            StockTransaction::create([
                'user_id' => $user->id,
                'stock_id' => $stock->id,
                'type' => 'sell',
                'shares' => $shares,
                'price_per_share' => $stock->current_price,
                'total_amount' => $totalRevenue,
                'fees' => $fees,
                'executed_at' => now(),
            ]);

            $remainingShares = $userStock->shares - $shares;
            
            if ($remainingShares <= 0) {
                $userStock->delete();
            } else {
                $proportionSold = $shares / $userStock->shares;
                $investmentSold = $userStock->total_invested * $proportionSold;
                
                $userStock->shares = $remainingShares;
                $userStock->total_invested -= $investmentSold;
                $userStock->current_value = $remainingShares * $stock->current_price;
                $userStock->profit_loss = $userStock->current_value - $userStock->total_invested;
                $userStock->save();
            }

            DB::commit();
            
            $this->applyModuleHook('OnStockSell', [
                'user' => $user,
                'stock' => $stock,
                'shares' => $shares,
                'revenue' => $totalRevenue,
            ]);

            return [
                'shares_sold' => $shares,
                'price_per_share' => $stock->current_price,
                'total_revenue' => $totalRevenue,
                'fees' => $fees,
                'net_revenue' => $netRevenue,
                'new_balance' => $user->fresh()->cash,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Update stock prices (cron job)
     */
    public function updatePrices(): void
    {
        $stocks = Stock::where('is_active', true)->get();
        
        foreach ($stocks as $stock) {
            $volatility = $stock->volatility ?? 0.05;
            $change = (mt_rand(-100, 100) / 100) * $volatility;
            $newPrice = max(1, $stock->current_price * (1 + $change));
            
            // Record history
            StockPriceHistory::create([
                'stock_id' => $stock->id,
                'price' => $newPrice,
                'recorded_at' => now(),
            ]);
            
            $stock->previous_price = $stock->current_price;
            $stock->current_price = round($newPrice, 2);
            $stock->price_change = $stock->current_price - $stock->previous_price;
            $stock->price_change_percentage = $stock->previous_price > 0 
                ? (($stock->price_change / $stock->previous_price) * 100) 
                : 0;
            $stock->save();
            
            $this->applyModuleHook('OnPriceUpdate', [
                'stock' => $stock,
                'old_price' => $stock->previous_price,
                'new_price' => $stock->current_price,
            ]);
        }
    }
    
    /**
     * Get transaction history for a user
     */
    public function getTransactionHistory(User $user, int $limit = 50)
    {
        return StockTransaction::where('user_id', $user->id)
            ->with('stock')
            ->orderBy('executed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
