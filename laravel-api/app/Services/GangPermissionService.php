<?php

namespace App\Services;

use App\Models\Gang;
use App\Models\GangPermission;
use App\Models\GangLog;
use App\Models\User;

class GangPermissionService
{
    /**
     * Create default permissions for a new gang member.
     */
    public function createMemberPermissions(Gang $gang, User $player, string $role = 'member'): GangPermission
    {
        $permissions = [
            'gang_id' => $gang->id,
            'user_id' => $player->id,
            'role' => $role,
        ];

        // Set default permissions based on role
        $permissions = array_merge($permissions, $this->getDefaultPermissionsForRole($role));

        return GangPermission::create($permissions);
    }

    /**
     * Get default permissions based on role.
     */
    private function getDefaultPermissionsForRole(string $role): array
    {
        return match($role) {
            'boss' => [
                'can_invite' => true,
                'can_kick' => true,
                'can_promote' => true,
                'can_demote' => true,
                'can_manage_bank' => true,
                'can_manage_property' => true,
                'can_manage_wars' => true,
                'can_send_mass_message' => true,
            ],
            'underboss' => [
                'can_invite' => true,
                'can_kick' => true,
                'can_promote' => true,
                'can_demote' => true,
                'can_manage_bank' => true,
                'can_manage_property' => true,
                'can_manage_wars' => true,
                'can_send_mass_message' => true,
            ],
            'captain' => [
                'can_invite' => true,
                'can_kick' => false,
                'can_promote' => false,
                'can_demote' => false,
                'can_manage_bank' => false,
                'can_manage_property' => false,
                'can_manage_wars' => false,
                'can_send_mass_message' => true,
            ],
            'member' => [
                'can_invite' => false,
                'can_kick' => false,
                'can_promote' => false,
                'can_demote' => false,
                'can_manage_bank' => false,
                'can_manage_property' => false,
                'can_manage_wars' => false,
                'can_send_mass_message' => false,
            ],
        };
    }

    /**
     * Update player's role.
     */
    public function updateRole(Gang $gang, User $player, string $newRole, User $actor): bool
    {
        $permission = GangPermission::where('gang_id', $gang->id)
            ->where('user_id', $player->id)
            ->first();

        if (!$permission) {
            return false;
        }

        $oldRole = $permission->role;
        $permission->role = $newRole;
        
        // Update permissions based on new role
        $newPermissions = $this->getDefaultPermissionsForRole($newRole);
        $permission->fill($newPermissions);
        $permission->save();

        // Log the action
        $this->logAction($gang, $actor, $player, $oldRole < $newRole ? 'promoted' : 'demoted', [
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);

        return true;
    }

    /**
     * Update specific permissions for a player.
     */
    public function updatePermissions(Gang $gang, User $player, array $permissions, User $actor): bool
    {
        $permission = GangPermission::where('gang_id', $gang->id)
            ->where('user_id', $player->id)
            ->first();

        if (!$permission) {
            return false;
        }

        $permission->update($permissions);

        // Log the action
        $this->logAction($gang, $actor, $player, 'permissions_updated', [
            'updated_permissions' => array_keys($permissions),
        ]);

        return true;
    }

    /**
     * Check if player has specific permission.
     */
    public function hasPermission(Gang $gang, User $player, string $permission): bool
    {
        $gangPermission = GangPermission::where('gang_id', $gang->id)
            ->where('user_id', $player->id)
            ->first();

        if (!$gangPermission) {
            return false;
        }

        // Boss has all permissions
        if ($gangPermission->isBoss()) {
            return true;
        }

        return $gangPermission->$permission ?? false;
    }

    /**
     * Get player's permissions in gang.
     */
    public function getPermissions(Gang $gang, User $player): ?GangPermission
    {
        return GangPermission::where('gang_id', $gang->id)
            ->where('user_id', $player->id)
            ->first();
    }

    /**
     * Get all gang members with their permissions.
     */
    public function getGangMembers(Gang $gang)
    {
        return GangPermission::where('gang_id', $gang->id)
            ->with('user')
            ->orderByRaw("FIELD(role, 'boss', 'underboss', 'captain', 'member')")
            ->get();
    }

    /**
     * Remove player's permissions (when leaving/kicked).
     */
    public function removePermissions(Gang $gang, User $player): bool
    {
        return GangPermission::where('gang_id', $gang->id)
            ->where('user_id', $player->id)
            ->delete();
    }

    /**
     * Log gang action.
     */
    public function logAction(
        Gang $gang,
        ?User $actor,
        ?User $target,
        string $action,
        ?array $details = null,
        ?string $ip = null
    ): GangLog {
        return GangLog::create([
            'gang_id' => $gang->id,
            'user_id' => $actor?->id,
            'target_player_id' => $target?->id,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Get gang activity logs.
     */
    public function getGangLogs(Gang $gang, int $limit = 100)
    {
        return GangLog::where('gang_id', $gang->id)
            ->with(['user', 'targetPlayer'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get player's activity in gang.
     */
    public function getPlayerActivity(Gang $gang, User $player, int $limit = 50)
    {
        return GangLog::where('gang_id', $gang->id)
            ->where(function($query) use ($player) {
                $query->where('user_id', $player->id)
                      ->orWhere('target_player_id', $player->id);
            })
            ->with(['user', 'targetPlayer'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
