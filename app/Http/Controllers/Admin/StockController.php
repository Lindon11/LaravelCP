<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::orderBy('symbol')->get();
        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string|max:10|unique:stocks',
            'name' => 'required|string|max:255',
            'sector' => 'required|string|max:255',
            'description' => 'required|string',
            'current_price' => 'required|numeric|min:0',
            'shares_available' => 'required|integer|min:1',
        ]);
        $stock = Stock::create($request->all());
        return response()->json(['message' => 'Stock created', 'stock' => $stock], 201);
    }

    public function show($id)
    {
        $stock = Stock::with('priceHistory')->findOrFail($id);
        return response()->json($stock);
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->update($request->all());
        return response()->json(['message' => 'Stock updated', 'stock' => $stock]);
    }

    public function updatePrice(Request $request, $id)
    {
        $request->validate(['price' => 'required|numeric|min:0']);
        $stock = Stock::findOrFail($id);
        $stock->update(['current_price' => $request->price]);
        return response()->json(['message' => 'Price updated', 'stock' => $stock]);
    }

    public function allTransactions()
    {
        $transactions = StockTransaction::with(['user', 'stock'])->orderBy('executed_at', 'desc')->limit(100)->get();
        return response()->json($transactions);
    }

    public function statistics()
    {
        return response()->json([
            'total_volume' => StockTransaction::sum('total_amount'),
            'transactions_today' => StockTransaction::whereDate('executed_at', today())->count(),
            'top_stocks' => Stock::orderBy('shares_traded', 'desc')->limit(5)->get(),
        ]);
    }

    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        
        // Check if stock has active holdings
        if ($stock->holdings()->exists()) {
            return response()->json(['error' => 'Cannot delete stock with active holdings'], 400);
        }
        
        $stock->delete();
        return response()->json(['message' => 'Stock deleted']);
    }
}
