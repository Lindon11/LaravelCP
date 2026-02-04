<?php

namespace App\Plugins\Bank\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Bank\Services\BankService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankController extends Controller
{
    protected $bankService;

    public function __construct(BankService $bankService)
    {
        $this->bankService = $bankService;
    }

    /**
     * Display the bank page
     */
    public function index()
    {
        $player = auth()->user();
        
        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found. Please contact support.');
        }
        
        $taxRate = $this->bankService->getTaxRate();

        return Inertia::render('Modules/Bank/Index', [
            'player' => $player,
            'taxRate' => $taxRate,
        ]);
    }

    /**
     * Deposit money into bank
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $player = auth()->user();
        $amount = $request->input('amount');

        $result = $this->bankService->deposit($player, $amount);

        if ($result['success']) {
            return redirect()->route('bank.index')->with('success', $result['message']);
        } else {
            return redirect()->route('bank.index')->with('error', $result['message']);
        }
    }

    /**
     * Withdraw money from bank
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $player = auth()->user();
        $amount = $request->input('amount');

        $result = $this->bankService->withdraw($player, $amount);

        if ($result['success']) {
            return redirect()->route('bank.index')->with('success', $result['message']);
        } else {
            return redirect()->route('bank.index')->with('error', $result['message']);
        }
    }

    /**
     * Transfer money to another player
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string',
            'amount' => 'required|integer|min:1',
        ]);

        $player = auth()->user();
        $recipient = $request->input('recipient');
        $amount = $request->input('amount');

        $result = $this->bankService->transfer($player, $recipient, $amount);

        if ($result['success']) {
            return redirect()->route('bank.index')->with('success', $result['message']);
        } else {
            return redirect()->route('bank.index')->with('error', $result['message']);
        }
    }
}
