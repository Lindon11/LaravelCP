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
        $treatments = $this->module->getAvailableTreatments($user);
        
        return response()->json([
            'treatments' => $treatments,
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
            'treatment_id' => 'required|exists:hospital_treatments,id'
        ]);
        
        $user = $request->user();
        $result = $this->module->heal($user, $request->treatment_id);
        
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
}
