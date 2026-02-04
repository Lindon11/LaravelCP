<?php

namespace App\Plugins\Stocks\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Stocks\StocksModule;
use Illuminate\Http\Request;

class StocksController extends Controller
{
    protected StocksModule $module;
    
    public function __construct()
    {
        $this->module = new StocksModule();
    }
    
    public function index()
    {
        $stocks = $this->module->getAllStocks();
        return response()->json(['stocks' => $stocks]);
    }

    public function show($id)
    {
        $stock = $this->module->getStockDetails($id);
        return response()->json($stock);
    }

    public function portfolio(Request $request)
    {
        $portfolio = $this->module->getUserPortfolio($request->user());
        return response()->json(['portfolio' => $portfolio]);
    }

    public function buy(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'shares' => 'required|integer|min:1'
        ]);
        
        try {
            $result = $this->module->buyStock(
                $request->user(),
                $request->stock_id,
                $request->shares
            );
            return response()->json(['message' => 'Stocks purchased!', ...$result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sell(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'shares' => 'required|integer|min:1'
        ]);
        
        try {
            $result = $this->module->sellStock(
                $request->user(),
                $request->stock_id,
                $request->shares
            );
            return response()->json(['message' => 'Stocks sold!', ...$result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function history(Request $request)
    {
        $history = $this->module->getTransactionHistory($request->user());
        return response()->json(['history' => $history]);
    }
}
