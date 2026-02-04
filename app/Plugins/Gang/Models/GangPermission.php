<?php

namespace App\Plugins\Gang\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangPermission extends Model
{
    protected $fillable = [
        'gang_id',
        'user_id',
        'role',
        'can_invite',
        'can_kick',
        'can_promote',
        'can_demote',
        'can_manage_bank',
        'can_manage_property',
        'can_manage_wars',
        'can_send_mass_message',
    ];

    protected $casts = [
        'can_invite' => 'boolean',
        'can_kick' => 'boolean',
        'can_promote' => 'boolean',
        'can_demote' => 'boolean',
        'can_manage_bank' => 'boolean',
        'can_manage_property' => 'boolean',
        'can_manage_wars' => 'boolean',
        'can_send_mass_message' => 'boolean',
    ];

    /**
     * Get the gang that owns the permission.
     */
    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    /**
     * Get the user that owns the permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if player is boss.
     */
    public function isBoss(): bool
    {
        return $this->role === 'boss';
    }

    /**
     * Check if player is underboss.
     */
    public function isUnderboss(): bool
    {
        return $this->role === 'underboss';
    }

    /**
     * Check if player is captain.
     */
    public function isCaptain(): bool
    {
        return $this->role === 'captain';
    }

    /**
     * Check if player is regular member.
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if player has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Boss and Underboss have all permissions
        if (in_array($this->role, ['boss', 'underboss'])) {
            return true;
        }
        
        return $this->$permission ?? false;
    }

    /**
     * Check if player has leadership role (boss or underboss).
     */
    public function isLeadership(): bool
    {
        return in_array($this->role, ['boss', 'underboss']);
    }

    /**
     * Check if player has management role (boss, underboss, or captain).
     */
    public function isManagement(): bool
    {
        return in_array($this->role, ['boss', 'underboss', 'captain']);
    }

    /**
     * Get all permissions as array.
     */
    public function getPermissions(): array
    {
        return [
            'role' => $this->role,
            'can_invite' => $this->can_invite,
            'can_kick' => $this->can_kick,
            'can_promote' => $this->can_promote,
            'can_demote' => $this->can_demote,
            'can_manage_bank' => $this->can_manage_bank,
            'can_manage_property' => $this->can_manage_property,
            'can_manage_wars' => $this->can_manage_wars,
            'can_send_mass_message' => $this->can_send_mass_message,
        ];
    }
}
