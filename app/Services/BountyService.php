<?php

namespace App\Services;

use App\Models\Bounty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BountyService
{
    const MIN_BOUNTY_AMOUNT = 10000; // $10,000 minimum
    const BOUNTY_FEE_PERCENTAGE = 10; // 10% fee

    public function placeBounty(User $placer, User $target, float $amount, ?string $reason = null): array
    {
        if ($placer->id === $target->id) {
            throw new \Exception('You cannot place a bounty on yourself.');
        }

        if ($amount < self::MIN_BOUNTY_AMOUNT) {
            throw new \Exception('Minimum bounty is $' . number_format(self::MIN_BOUNTY_AMOUNT));
        }

        $fee = $amount * (self::BOUNTY_FEE_PERCENTAGE / 100);
        $totalCost = $amount + $fee;

        if ($placer->cash < $totalCost) {
            throw new \Exception('Not enough cash. Need $' . number_format($totalCost) . ' (bounty + ' . self::BOUNTY_FEE_PERCENTAGE . '% fee)');
        }

        return DB::transaction(function () use ($placer, $target, $amount, $reason, $totalCost) {
            // Deduct cash
            $placer->decrement('cash', $totalCost);

            // Create or add to existing bounty
            $bounty = Bounty::where('target_id', $target->id)
                ->where('status', 'active')
                ->first();

            if ($bounty) {
                $bounty->increment('amount', $amount);
            } else {
                $bounty = Bounty::create([
                    'target_id' => $target->id,
                    'placed_by' => $placer->id,
                    'amount' => $amount,
                    'status' => 'active',
                    'reason' => $reason,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Bounty of $' . number_format($amount) . ' placed on ' . $target->username,
                'bounty' => $bounty,
            ];
        });
    }

    public function claimBounty(User $killer, User $victim): ?array
    {
        $bounty = Bounty::where('target_id', $victim->id)
            ->where('status', 'active')
            ->first();

        if (!$bounty) {
            return null;
        }

        return DB::transaction(function () use ($bounty, $killer) {
            $bounty->update([
                'claimed_by' => $killer->id,
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);

            $killer->increment('cash', $bounty->amount);

            return [
                'success' => true,
                'amount' => $bounty->amount,
                'message' => 'You claimed a bounty of $' . number_format($bounty->amount),
            ];
        });
    }

    public function getActiveBounties()
    {
        return Bounty::where('status', 'active')
            ->with(['target', 'placer'])
            ->orderByDesc('amount')
            ->get()
            ->map(function ($bounty) {
                return [
                    'id' => $bounty->id,
                    'target' => $bounty->target->username,
                    'target_id' => $bounty->target_id,
                    'amount' => $bounty->amount,
                    'reason' => $bounty->reason,
                    'placed_at' => $bounty->created_at->diffForHumans(),
                ];
            });
    }

    public function getMyBounties(User $player)
    {
        return [
            'placed' => Bounty::where('placed_by', $player->id)
                ->with('target')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($b) => [
                    'target' => $b->target->username,
                    'amount' => $b->amount,
                    'status' => $b->status,
                    'placed_at' => $b->created_at->diffForHumans(),
                ]),
            'on_me' => Bounty::where('target_id', $player->id)
                ->where('status', 'active')
                ->sum('amount'),
        ];
    }
}
