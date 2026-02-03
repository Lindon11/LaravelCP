<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class BankService
{
    /**
     * Bank tax percentage (15% default from legacy)
     */
    protected int $taxRate = 15;

    /**
     * Deposit money into bank (with tax)
     */
    public function deposit(User $player, int $amount): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => "You can't deposit negative cash.",
            ];
        }

        return DB::transaction(function () use ($player, $amount) {
            // Lock the user row for update to prevent race conditions
            $player = User::where('id', $player->id)->lockForUpdate()->first();

            if ($player->cash < $amount) {
                return [
                    'success' => false,
                    'message' => "You don't have enough money for this transaction!",
                ];
            }

            // Calculate tax (15% loss when depositing)
            $taxMultiplier = (100 - $this->taxRate) / 100;
            $depositedAmount = (int)($amount * $taxMultiplier);
            $taxAmount = $amount - $depositedAmount;

            // Update player balances
            $player->cash -= $amount;
            $player->bank += $depositedAmount;
            $player->save();

            return [
                'success' => true,
                'message' => "You sent $" . number_format($amount) . " to your money launderer. He deposited $" . number_format($depositedAmount) . " into your bank account!",
                'amount' => $amount,
                'deposited' => $depositedAmount,
                'tax' => $taxAmount,
            ];
        });
    }

    /**
     * Withdraw money from bank (no tax)
     */
    public function withdraw(User $player, int $amount): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => "You can't withdraw negative cash.",
            ];
        }

        return DB::transaction(function () use ($player, $amount) {
            // Lock the user row for update
            $player = User::where('id', $player->id)->lockForUpdate()->first();

            if ($player->bank < $amount) {
                return [
                    'success' => false,
                    'message' => "You don't have enough money in your bank for this transaction!",
                ];
            }

            // No tax on withdrawals
            $player->bank -= $amount;
            $player->cash += $amount;
            $player->save();

            return [
                'success' => true,
                'message' => "You have withdrawn $" . number_format($amount) . "!",
                'amount' => $amount,
            ];
        });
    }

    /**
     * Transfer money to another player
     */
    public function transfer(User $sender, string $recipientUsername, int $amount): array
    {
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => "How much money do you want to send?",
            ];
        }

        // Find recipient by username first (outside transaction)
        $recipient = User::where('username', $recipientUsername)->first();

        if (!$recipient) {
            return [
                'success' => false,
                'message' => "This user does not exist.",
            ];
        }

        if ($recipient->id === $sender->id) {
            return [
                'success' => false,
                'message' => "You can't send money to yourself!",
            ];
        }

        return DB::transaction(function () use ($sender, $recipient, $amount) {
            // Lock both users to prevent race conditions
            // Always lock in consistent order (by ID) to prevent deadlocks
            $userIds = [$sender->id, $recipient->id];
            sort($userIds);
            
            $users = User::whereIn('id', $userIds)->lockForUpdate()->get()->keyBy('id');
            $lockedSender = $users[$sender->id];
            $lockedRecipient = $users[$recipient->id];

            if ($lockedSender->cash < $amount) {
                return [
                    'success' => false,
                    'message' => "You don't have that much money.",
                ];
            }

            // Transfer money
            $lockedSender->cash -= $amount;
            $lockedSender->save();

            $lockedRecipient->cash += $amount;
            $lockedRecipient->save();

            // Send notification to recipient
            app(NotificationService::class)->moneyReceived($lockedRecipient, $lockedSender, $amount);

            return [
                'success' => true,
                'message' => "You have sent $" . number_format($amount) . " to {$lockedRecipient->username}!",
                'amount' => $amount,
                'recipient' => $lockedRecipient->username,
            ];
        });
    }

    /**
     * Get bank tax rate
     */
    public function getTaxRate(): int
    {
        return $this->taxRate;
    }

    /**
     * Calculate deposit amount after tax
     */
    public function calculateDepositAmount(int $amount): array
    {
        $taxMultiplier = (100 - $this->taxRate) / 100;
        $depositedAmount = (int)($amount * $taxMultiplier);
        $taxAmount = $amount - $depositedAmount;

        return [
            'original' => $amount,
            'deposited' => $depositedAmount,
            'tax' => $taxAmount,
            'tax_rate' => $this->taxRate,
        ];
    }
}
