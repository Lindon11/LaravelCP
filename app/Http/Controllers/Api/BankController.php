<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Bank\BankModule;
use Illuminate\Http\Request;

class BankController extends Controller
{
    protected BankModule $module;
    
    public function __construct()
    {
        $this->module = new BankModule();
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'player' => [
                'cash' => $user->cash,
                'bank' => $user->bank,
            ],
            'settings' => [
                'transfer_fee' => $this->module->getTaxRate(),
            ]
        ]);
    }
    
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->deposit($user, $request->amount);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Deposit completed',
            'player' => [
                'cash' => $user->cash,
                'bank' => $user->bank,
            ],
        ]);
    }
    
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->withdraw($user, $request->amount);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Withdrawal completed',
            'player' => [
                'cash' => $user->cash,
                'bank' => $user->bank,
            ],
        ]);
    }
    
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1'
        ]);
        
        $user = $request->user();
        $result = $this->module->transfer($user, $request->recipient_id, $request->amount);
        
        $user->refresh();
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Transfer completed',
            'player' => [
                'cash' => $user->cash,
                'bank' => $user->bank,
            ],
        ]);
    }
}
