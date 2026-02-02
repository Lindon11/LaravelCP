<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockMarketService;
use Illuminate\Http\Request;

class StockMarketController extends Controller
{
    protected $stockService;

    public function __construct(StockMarketService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $stocks = $this->stockService->getAllStocks();
        return response()->json(['stocks' => $stocks]);
    }

    public function show($id)
    {
        $stock = $this->stockService->getStockDetails($id);
        return response()->json($stock);
    }

    public function portfolio(Request $request)
    {
        $portfolio = $this->stockService->getUserPortfolio($request->user());
        return response()->json(['portfolio' => $portfolio]);
    }

    public function buy(Request $request)
    {
        $request->validate(['stock_id' => 'required|exists:stocks,id', 'shares' => 'required|integer|min:1']);
        try {
            $result = $this->stockService->buyStock($request->user(), $request->stock_id, $request->shares);
            return response()->json(['message' => 'Stocks purchased!', ...$result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sell(Request $request)
    {
        $request->validate(['stock_id' => 'required|exists:stocks,id', 'shares' => 'required|integer|min:1']);
        try {
            $result = $this->stockService->sellStock($request->user(), $request->stock_id, $request->shares);
            return response()->json(['message' => 'Stocks sold!', ...$result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
