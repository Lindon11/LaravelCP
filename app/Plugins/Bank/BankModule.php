<?php

namespace App\Plugins\Bank;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Bank\Services\BankService;

/**
 * Bank Module
 * 
 * Handles banking system - deposits, withdrawals, and transfers
 */
class BankModule extends Plugin
{
    protected string $name = 'Bank';
    
    protected BankService $bankService;
    
    public function construct(): void
    {
        $this->bankService = app(BankService::class);
        
        $this->config = [
            'tax_rate' => 0.05, // 5% tax
            'min_transfer' => 1,
        ];
    }
    
    /**
     * Get tax rate
     */
    public function getTaxRate(): float
    {
        $taxRate = $this->bankService->getTaxRate();
        
        return $this->applyModuleHook('alterTaxRate', [
            'tax_rate' => $taxRate,
        ])['tax_rate'] ?? $taxRate;
    }
    
    /**
     * Deposit money into bank
     */
    public function deposit(User $user, int $amount): array
    {
        // Apply hooks before deposit
        $this->applyModuleHook('beforeBankDeposit', [
            'user' => $user,
            'amount' => $amount,
        ]);
        
        $result = $this->bankService->deposit($user, $amount);
        
        // Apply hooks after deposit
        if ($result['success']) {
            $this->applyModuleHook('afterBankDeposit', [
                'user' => $user,
                'amount' => $amount,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Withdraw money from bank
     */
    public function withdraw(User $user, int $amount): array
    {
        // Apply hooks before withdrawal
        $this->applyModuleHook('beforeBankWithdraw', [
            'user' => $user,
            'amount' => $amount,
        ]);
        
        $result = $this->bankService->withdraw($user, $amount);
        
        // Apply hooks after withdrawal
        if ($result['success']) {
            $this->applyModuleHook('afterBankWithdraw', [
                'user' => $user,
                'amount' => $amount,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Transfer money to another player
     */
    public function transfer(User $user, string $recipient, int $amount): array
    {
        // Apply hooks before transfer
        $this->applyModuleHook('beforeBankTransfer', [
            'user' => $user,
            'recipient' => $recipient,
            'amount' => $amount,
        ]);
        
        $result = $this->bankService->transfer($user, $recipient, $amount);
        
        // Apply hooks after transfer
        if ($result['success']) {
            $this->applyModuleHook('afterBankTransfer', [
                'user' => $user,
                'recipient' => $recipient,
                'amount' => $amount,
            ]);
        }
        
        return $result;
    }
}
