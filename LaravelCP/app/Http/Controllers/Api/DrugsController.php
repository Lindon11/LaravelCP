<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Drugs\DrugsModule;
use Illuminate\Http\Request;

class DrugsController extends Controller
{
    protected DrugsModule $module;
    
    public function __construct()
    {
        $this->module = new DrugsModule();
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        $drugPrices = $this->module->getDrugPrices($user);
        
        return response()->json([
            'drugPrices' => $drugPrices,
            'player' => [
                'cash' => $user->cash,
                'location' => $user->location,
            ]
        ]);
    }
    
    public function buy(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->buy($user, $request->drug_id, $request->quantity);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Purchase completed',
            'player' => [
                'cash' => $user->cash,
            ],
        ]);
    }
    
    public function sell(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->sell($user, $request->drug_id, $request->quantity);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Sale completed',
            'player' => [
                'cash' => $user->cash,
            ],
        ]);
    }
}
