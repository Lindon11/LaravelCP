<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Hospital\HospitalModule;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    protected HospitalModule $module;
    
    public function __construct()
    {
        $this->module = new HospitalModule();
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        $fullHealCost = $this->module->calculateFullHealCost($user);
        
        return response()->json([
            'fullHealCost' => $fullHealCost,
            'costPerHp' => 100, // Default cost per HP
            'player' => [
                'health' => $user->health,
                'max_health' => $user->max_health,
                'cash' => $user->cash,
            ]
        ]);
    }
    
    public function heal(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->heal($user, $request->amount);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Treatment completed',
            'player' => [
                'health' => $user->health,
                'max_health' => $user->max_health,
                'cash' => $user->cash,
            ],
        ]);
    }
    
    public function healFull(Request $request)
    {
        $user = $request->user();
        $result = $this->module->healFull($user);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Fully healed!',
            'cost' => $result['cost'] ?? 0,
            'player' => [
                'health' => $user->health,
                'max_health' => $user->max_health,
                'cash' => $user->cash,
            ],
        ]);
    }
}
