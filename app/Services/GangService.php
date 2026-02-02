<?php

namespace App\Services;

use App\Models\User;
use App\Models\Gang;

class GangService
{
    /**
     * Cost to create a gang
     */
    const GANG_CREATION_COST = 1000000; // $1 million

    /**
     * Create a new gang
     */
    public function createGang(User $player, string $name, string $tag): array
    {
        if ($player->gang_id) {
            return ['success' => false, 'message' => 'You are already in a gang!'];
        }

        if ($player->cash < self::GANG_CREATION_COST) {
            return ['success' => false, 'message' => 'You need $' . number_format(self::GANG_CREATION_COST) . ' to create a gang!'];
        }

        if (Gang::where('name', $name)->exists()) {
            return ['success' => false, 'message' => 'A gang with this name already exists!'];
        }

        if (Gang::where('tag', $tag)->exists()) {
            return ['success' => false, 'message' => 'This gang tag is already taken!'];
        }

        // Create gang
        $gang = Gang::create([
            'name' => $name,
            'tag' => $tag,
            'leader_id' => $player->id,
            'bank' => 0,
            'respect' => 0,
            'max_members' => 10,
        ]);

        // Deduct cost and join gang
        $player->cash -= self::GANG_CREATION_COST;
        $player->gang_id = $gang->id;
        $player->save();

        return [
            'success' => true,
            'gang' => $gang,
            'message' => "Gang '{$name}' [{$tag}] created successfully!",
        ];
    }

    /**
     * Leave gang
     */
    public function leaveGang(User $player): array
    {
        if (!$player->gang_id) {
            return ['success' => false, 'message' => 'You are not in a gang!'];
        }

        $gang = Gang::find($player->gang_id);

        if ($gang->leader_id == $player->id) {
            $memberCount = User::where('gang_id', $gang->id)->count();
            if ($memberCount > 1) {
                return ['success' => false, 'message' => 'You must transfer leadership or disband the gang first!'];
            }
            // Last member and leader - delete gang
            $gang->delete();
        }

        $player->gang_id = null;
        $player->save();

        return [
            'success' => true,
            'message' => 'You have left the gang!',
        ];
    }

    /**
     * Kick member (leader only)
     */
    public function kickMember(User $leader, User $target): array
    {
        if (!$leader->gang_id) {
            return ['success' => false, 'message' => 'You are not in a gang!'];
        }

        $gang = Gang::find($leader->gang_id);

        if ($gang->leader_id != $leader->id) {
            return ['success' => false, 'message' => 'Only the gang leader can kick members!'];
        }

        if ($target->gang_id != $gang->id) {
            return ['success' => false, 'message' => 'This player is not in your gang!'];
        }

        if ($target->id == $leader->id) {
            return ['success' => false, 'message' => 'You cannot kick yourself!'];
        }

        $target->gang_id = null;
        $target->save();

        return [
            'success' => true,
            'message' => "Kicked {$target->username} from the gang!",
        ];
    }

    /**
     * Deposit to gang bank
     */
    public function depositToGangBank(User $player, int $amount): array
    {
        if (!$player->gang_id) {
            return ['success' => false, 'message' => 'You are not in a gang!'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount!'];
        }

        if ($player->cash < $amount) {
            return ['success' => false, 'message' => 'You do not have enough cash!'];
        }

        $gang = Gang::find($player->gang_id);

        $player->cash -= $amount;
        $player->save();

        $gang->bank += $amount;
        $gang->save();

        return [
            'success' => true,
            'message' => 'Deposited $' . number_format($amount) . ' to gang bank!',
        ];
    }

    /**
     * Withdraw from gang bank (leader only)
     */
    public function withdrawFromGangBank(User $player, int $amount): array
    {
        if (!$player->gang_id) {
            return ['success' => false, 'message' => 'You are not in a gang!'];
        }

        $gang = Gang::find($player->gang_id);

        if ($gang->leader_id != $player->id) {
            return ['success' => false, 'message' => 'Only the gang leader can withdraw!'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount!'];
        }

        if ($gang->bank < $amount) {
            return ['success' => false, 'message' => 'Gang bank does not have enough funds!'];
        }

        $gang->bank -= $amount;
        $gang->save();

        $player->cash += $amount;
        $player->save();

        return [
            'success' => true,
            'message' => 'Withdrew $' . number_format($amount) . ' from gang bank!',
        ];
    }
}
