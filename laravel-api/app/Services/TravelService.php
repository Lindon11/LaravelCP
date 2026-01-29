<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TravelService
{
    public function travel(User $player, Location $destination): array
    {
        $currentLocation = $player->location_id;

        if ($currentLocation === $destination->id) {
            throw new \Exception('You are already in ' . $destination->name);
        }

        if ($player->level < $destination->required_level) {
            throw new \Exception('You need to be level ' . $destination->required_level . ' to travel to ' . $destination->name);
        }

        if ($player->cash < $destination->travel_cost) {
            throw new \Exception('You need $' . number_format($destination->travel_cost) . ' to travel to ' . $destination->name);
        }

        return DB::transaction(function () use ($player, $destination) {
            // Deduct travel cost
            $player->decrement('cash', $destination->travel_cost);

            // Update location
            $player->update(['location_id' => $destination->id]);

            return [
                'success' => true,
                'message' => 'You traveled to ' . $destination->name . ' for $' . number_format($destination->travel_cost),
            ];
        });
    }

    public function getAvailableLocations()
    {
        return Location::orderBy('required_level')
            ->orderBy('name')
            ->get();
    }

    public function getPlayersInLocation(Location $location)
    {
        return User::where('location_id', $location->id)
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->whereNotNull('last_active_at')
                    ->where('last_active_at', '>=', now()->subMinutes(15));
            })
            ->get()
            ->map(function ($player) {
                return [
                    'id' => $player->id,
                    'username' => $player->username,
                    'level' => $player->level,
                    'rank' => $player->rank,
                ];
            });
    }
}
