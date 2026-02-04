<?php

namespace App\Plugins\Gang;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Gang\Models\Gang;

/**
 * Gang Module
 * 
 * Handles gang system - create, join, manage gangs with banking
 * Allows players to form groups for organized crime and social interaction
 */
class GangModule extends Plugin
{
    protected string $name = 'Gang';
    
    public function construct(): void
    {
        $this->config = [
            'creation_cost' => 100000,
            'max_members_base' => 10,
            'bank_interest_rate' => 0.01,
        ];
    }
    
    /**
     * Get gang information with member data
     */
    public function getGangInfo(Gang $gang): array
    {
        return $this->applyModuleHook('alterGangData', [
            'gang' => $gang,
            'members' => $gang->members,
            'leader' => $gang->leader,
            'bank_balance' => $gang->bank,
            'respect' => $gang->respect,
            'level' => $gang->level,
        ]);
    }
    
    /**
     * Create a new gang
     */
    public function createGang(User $user, string $name, string $tag): array
    {
        // Check if user already in gang
        if ($user->gang_id) {
            return $this->error('You are already in a gang');
        }
        
        // Check cost
        if ($user->cash < $this->config['creation_cost']) {
            return $this->error('Not enough cash to create a gang');
        }
        
        // Apply hooks before creation
        $this->applyModuleHook('beforeGangCreate', [
            'user' => $user,
            'name' => $name,
            'tag' => $tag,
        ]);
        
        // Create gang
        $gang = Gang::create([
            'name' => $name,
            'tag' => $tag,
            'leader_id' => $user->id,
            'max_members' => $this->config['max_members_base'],
        ]);
        
        // Deduct cost
        $user->cash -= $this->config['creation_cost'];
        $user->gang_id = $gang->id;
        $user->save();
        
        // Track action
        $this->trackAction($user, 'gang_created', ['gang_id' => $gang->id]);
        
        // Fire hook after creation
        $this->applyModuleHook('afterGangCreate', [
            'user' => $user,
            'gang' => $gang,
        ]);
        
        return $this->success("Gang '{$name}' created successfully!");
    }
    
    /**
     * Manage gang bank deposits
     */
    public function depositToBank(User $user, int $amount): array
    {
        if (!$user->gang_id) {
            return $this->error('You are not in a gang');
        }
        
        if ($user->cash < $amount) {
            return $this->error('Not enough cash');
        }
        
        $gang = Gang::find($user->gang_id);
        
        $user->cash -= $amount;
        $gang->bank += $amount;
        
        $user->save();
        $gang->save();
        
        $this->applyModuleHook('afterGangDeposit', [
            'user' => $user,
            'gang' => $gang,
            'amount' => $amount,
        ]);
        
        return $this->success("Deposited {$this->money($amount)} to gang bank");
    }
}
