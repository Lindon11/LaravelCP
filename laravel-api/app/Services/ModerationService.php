<?php

namespace App\Services;
use App\Models\User;
use App\Models\PlayerBan;
use App\Models\PlayerWarning;
use App\Models\IpBan;
use Illuminate\Support\Facades\DB;
class ModerationService
{
    public function banPlayer(User $player, User $admin, array $data): PlayerBan
    {
        DB::beginTransaction();
        try {
            // Deactivate any existing active bans
            PlayerBan::where('user_id', $player->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
            $ban = PlayerBan::create([
                'user_id' => $player->id,
                'banned_by' => $admin->id,
                'type' => $data['type'],
                'reason' => $data['reason'],
                'banned_at' => now(),
                'expires_at' => $data['type'] === 'temporary' ? $data['expires_at'] : null,
                'is_active' => true,
            ]);
            // Update player status
            $player->update(['is_banned' => true]);
            DB::commit();
            return $ban;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function unbanPlayer(PlayerBan $ban, User $admin, string $reason): bool
    {
        $ban->update([
            'is_active' => false,
            'unbanned_at' => now(),
            'unbanned_by' => $admin->id,
            'unban_reason' => $reason,
        ]);
        
        // Check if player has any other active bans
        $hasOtherBans = PlayerBan::where('user_id', $ban->user_id)
            ->where('id', '!=', $ban->id)
            ->exists();
        
        if (!$hasOtherBans) {
            $ban->user->update(['is_banned' => false]);
        }
        
        return true;
    }
    
    public function warnPlayer(User $player, User $admin, array $data): PlayerWarning
    {
        return PlayerWarning::create([
            'user_id' => $player->id,
            'issued_by' => $admin->id,
            'severity' => $data['severity'],
            'reason' => $data['reason'],
        ]);
    }
    
    public function banIp(string $ip, User $admin, array $data): IpBan
    {
        return IpBan::create([
            'ip_address' => $ip,
            'banned_by' => $admin->id,
            'banned_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);
    }
    
    public function unbanIp(IpBan $ipBan): bool
    {
        return $ipBan->update(['is_active' => false]);
    }
    
    public function getPlayerModerationHistory(User $player): array
    {
        return [
            'bans' => PlayerBan::where('user_id', $player->id)
                ->with(['bannedBy', 'unbannedBy'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'warnings' => PlayerWarning::where('user_id', $player->id)
                ->with('issuedBy')
                ->orderBy('created_at', 'desc')
                ->get(),
        ];
    }
    
    public function adjustPlayerStats(User $player, array $adjustments): Player
    {
        if (isset($adjustments['cash'])) {
            $player->cash += $adjustments['cash'];
        }
        
        if (isset($adjustments['bank'])) {
            $player->bank += $adjustments['bank'];
        }
        
        if (isset($adjustments['respect'])) {
            $player->respect += $adjustments['respect'];
        }
        
        if (isset($adjustments['experience'])) {
            $player->experience += $adjustments['experience'];
        }
        
        if (isset($adjustments['health'])) {
            $player->health = max(0, min(100, $adjustments['health']));
        }
        
        if (isset($adjustments['level'])) {
            $player->level = max(1, $adjustments['level']);
        }
        
        $player->save();
        return $player->fresh();
    }
    
    public function grantItem(User $player, int $itemId, int $quantity = 1): void
    {
        $inventory = $player->inventory()->where('item_id', $itemId)->first();
        
        if ($inventory) {
            $inventory->increment('quantity', $quantity);
        } else {
            $player->inventory()->create([
                'item_id' => $itemId,
                'quantity' => $quantity,
            ]);
        }
    }
}
