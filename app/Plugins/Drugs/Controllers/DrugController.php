<?php

namespace App\Plugins\Drugs\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Drugs\Services\DrugService;
use Illuminate\Http\Request;

class DrugController extends Controller
{
    protected $drugService;

    public function __construct(DrugService $drugService)
    {
        $this->drugService = $drugService;
    }

    public function index(Request $request)
    {
        $player = $request->user();

        $drugPrices = $this->drugService->getDrugPrices($player);
        $playerDrugs = $this->drugService->getPlayerDrugs($player);
        $totalValue = $this->drugService->getTotalDrugValue($player);

        return response()->json([
            'drugPrices' => $drugPrices,
            'playerDrugs' => $playerDrugs,
            'totalValue' => $totalValue,
            'player' => $player,
        ]);
    }

    public function buy(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|integer|exists:drugs,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        try {
            $player = $request->user();
            $result = $this->drugService->buyDrugs(
                $player,
                $request->drug_id,
                $request->quantity
            );

            return redirect()->back()->with('success',
                'Bought ' . $result['quantity'] . 'x ' . $result['drug']->name . ' for $' . number_format($result['cost']));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function sell(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|integer|exists:drugs,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        try {
            $player = $request->user();
            $result = $this->drugService->sellDrugs(
                $player,
                $request->drug_id,
                $request->quantity
            );

            return redirect()->back()->with('success',
                'Sold ' . $result['quantity'] . 'x ' . $result['drug']->name . ' for $' . number_format($result['earnings']));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
