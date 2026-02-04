<?php

namespace App\Plugins\Bounty;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Bounties\Models\Bounty;

/**
 * Bounty Module
 * 
 * Handles bounty system - place and claim bounties on rival players
 * Encourages PvP combat and rivalries
 */
class BountyModule extends Plugin
{
    protected string $name = 'Bounty';
    
    public function construct(): void
    {
        $this->config = [
            'min_bounty' => 10000,
            'max_bounty' => 10000000,
            'fee_percentage' => 0.10, // 10% fee
            'cooldown' => 300, // 5 minutes
        ];
    }
    
    /**
     * Get active bounties
     */
    public function getActiveBounties(): array
    {
        return Bounty::where('claimed', false)
            ->where('active', true)
            ->orderBy('amount', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($bounty) {
                return $this->applyModuleHook('alterBountyData', [
                    'bounty' => $bounty,
                    'target' => $bounty->target,
                    'placer' => $bounty->placer,
                    'amount' => $bounty->amount,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Place a bounty on a target
     */
    public function placeBounty(User $user, User $target, int $amount, ?string $reason = null): array
    {
        // Validation
        if ($user->id === $target->id) {
            return $this->error('You cannot place a bounty on yourself');
        }
        
        if ($amount < $this->config['min_bounty']) {
            return $this->error("Minimum bounty is {$this->money($this->config['min_bounty'])}");
        }
        
        if ($amount > $this->config['max_bounty']) {
            return $this->error("Maximum bounty is {$this->money($this->config['max_bounty'])}");
        }
        
        // Check cooldown
        if ($user->hasTimer('bounty_place')) {
            return $this->error('You must wait before placing another bounty');
        }
        
        // Calculate total cost with fee
        $fee = floor($amount * $this->config['fee_percentage']);
        $totalCost = $amount + $fee;
        
        if ($user->cash < $totalCost) {
            return $this->error("Not enough cash. Need {$this->money($totalCost)} (includes {$this->money($fee)} fee)");
        }
        
        // Apply hooks before placing
        $this->applyModuleHook('beforeBountyPlace', [
            'user' => $user,
            'target' => $target,
            'amount' => $amount,
            'reason' => $reason,
        ]);
        
        // Create bounty
        $bounty = Bounty::create([
            'placer_id' => $user->id,
            'target_id' => $target->id,
            'amount' => $amount,
            'reason' => $reason,
            'active' => true,
            'claimed' => false,
        ]);
        
        // Deduct cost
        $user->cash -= $totalCost;
        $user->save();
        
        // Set cooldown
        $user->setTimer('bounty_place', $this->config['cooldown']);
        
        // Track action
        $this->trackAction($user, 'bounty_placed', [
            'bounty_id' => $bounty->id,
            'target_id' => $target->id,
            'amount' => $amount,
        ]);
        
        // Fire hook after placing
        $this->applyModuleHook('afterBountyPlace', [
            'user' => $user,
            'target' => $target,
            'bounty' => $bounty,
        ]);
        
        return $this->success("Bounty of {$this->money($amount)} placed on {$target->username}!");
    }
    
    /**
     * Claim a bounty after killing target
     */
    public function claimBounty(User $killer, User $victim): array
    {
        $bounty = Bounty::where('target_id', $victim->id)
            ->where('active', true)
            ->where('claimed', false)
            ->first();
        
        if (!$bounty) {
            return $this->error('No active bounty on this target');
        }
        
        // Mark as claimed
        $bounty->claimed = true;
        $bounty->claimed_by = $killer->id;
        $bounty->claimed_at = now();
        $bounty->save();
        
        // Award bounty
        $killer->cash += $bounty->amount;
        $killer->save();
        
        // Apply hooks
        $this->applyModuleHook('afterBountyClaim', [
            'killer' => $killer,
            'victim' => $victim,
            'bounty' => $bounty,
        ]);
        
        return $this->success("Bounty claimed! Earned {$this->money($bounty->amount)}!");
    }
    
    /**
     * Get user's placed bounties
     */
    public function getMyBounties(User $user): array
    {
        return Bounty::where('placer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }
}
