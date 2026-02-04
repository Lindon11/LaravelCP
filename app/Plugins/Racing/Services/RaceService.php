<?php

namespace App\Plugins\Racing\Services;

use App\Plugins\Racing\Models\Race;
use App\Plugins\Racing\Models\RaceParticipant;
use App\Core\Models\User;
use App\Plugins\Inventory\Models\PlayerInventory;
use Illuminate\Support\Facades\DB;

class RaceService
{
    public function getAvailableRaces($player)
    {
        return Race::with(['location', 'participants.player', 'participants.vehicle.item'])
            ->where('location_id', $player->location_id)
            ->where('status', 'waiting')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRaceHistory($player, $limit = 10)
    {
        return RaceParticipant::with(['race.location', 'race.winner'])
            ->where('user_id', $player->id)
            ->whereHas('race', function ($query) {
                $query->where('status', 'finished');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function createRace($player, $data)
    {
        if (!isset($data['entry_fee']) || $data['entry_fee'] < 0) {
            throw new \Exception('Invalid entry fee');
        }

        $race = Race::create([
            'location_id' => $player->location_id,
            'name' => $data['name'] ?? 'Street Race',
            'description' => $data['description'] ?? null,
            'entry_fee' => $data['entry_fee'],
            'prize_pool' => 0,
            'min_participants' => $data['min_participants'] ?? 2,
            'max_participants' => $data['max_participants'] ?? 8,
            'status' => 'waiting',
        ]);

        return $race;
    }

    public function joinRace($player, $raceId, $vehicleId = null, $betAmount = 0)
    {
        $race = Race::with('participants')->findOrFail($raceId);

        // Validate race can be joined
        if (!$race->canJoin()) {
            throw new \Exception('This race is full or already started');
        }

        // Check if already in race
        if ($race->participants()->where('user_id', $player->id)->exists()) {
            throw new \Exception('You are already in this race');
        }

        // Validate entry fee
        if ($player->cash < $race->entry_fee) {
            throw new \Exception('You cannot afford the entry fee of $' . number_format($race->entry_fee));
        }

        // Validate bet amount
        if ($betAmount > 0 && $player->cash < ($race->entry_fee + $betAmount)) {
            throw new \Exception('You cannot afford to bet $' . number_format($betAmount));
        }

        // Validate vehicle if provided
        $vehicle = null;
        if ($vehicleId) {
            $vehicle = PlayerInventory::with('item')
                ->where('id', $vehicleId)
                ->where('user_id', $player->id)
                ->first();

            if (!$vehicle || $vehicle->item->type !== 'vehicle') {
                throw new \Exception('Invalid vehicle selected');
            }
        }

        DB::transaction(function () use ($race, $player, $vehicle, $betAmount) {
            // Deduct entry fee and bet
            $totalCost = $race->entry_fee + $betAmount;
            $player->decrement('cash', $totalCost);

            // Add to prize pool
            $race->increment('prize_pool', $race->entry_fee + $betAmount);

            // Create participant
            RaceParticipant::create([
                'race_id' => $race->id,
                'user_id' => $player->id,
                'vehicle_id' => $vehicle?->id,
                'bet_amount' => $betAmount,
            ]);
        });

        return $race->fresh();
    }

    public function startRace($raceId)
    {
        $race = Race::with(['participants.player', 'participants.vehicle.item'])->findOrFail($raceId);

        if (!$race->canStart()) {
            throw new \Exception('Race cannot start yet. Need at least ' . $race->min_participants . ' participants.');
        }

        DB::transaction(function () use ($race) {
            // Calculate race results
            $participants = $race->participants;
            $results = [];

            foreach ($participants as $participant) {
                $baseSpeed = $participant->user->speed ?? 10;
                $vehicleSpeed = $participant->vehicle?->item->stats['speed'] ?? 0;
                
                // Add randomness (±20%)
                $randomFactor = rand(80, 120) / 100;
                
                $totalSpeed = ($baseSpeed + $vehicleSpeed) * $randomFactor;
                
                // Calculate finish time (lower is better)
                $finishTime = rand(30000, 60000) - ($totalSpeed * 100);
                
                $results[] = [
                    'participant' => $participant,
                    'finish_time' => max(1000, $finishTime), // Min 1 second
                ];
            }

            // Sort by finish time (fastest first)
            usort($results, fn($a, $b) => $a['finish_time'] <=> $b['finish_time']);

            // Assign positions and calculate winnings
            $prizePool = $race->prize_pool;
            $winnerShare = 0.50; // 50% to 1st
            $secondShare = 0.30; // 30% to 2nd
            $thirdShare = 0.20;  // 20% to 3rd

            foreach ($results as $position => $result) {
                $participant = $result['participant'];
                $participant->update([
                    'position' => $position + 1,
                    'finish_time' => $result['finish_time'],
                ]);

                // Award winnings
                if ($position === 0) {
                    $winnings = (int) ($prizePool * $winnerShare);
                    $participant->update(['winnings' => $winnings]);
                    $participant->user->increment('cash', $winnings);
                } elseif ($position === 1 && count($results) > 1) {
                    $winnings = (int) ($prizePool * $secondShare);
                    $participant->update(['winnings' => $winnings]);
                    $participant->user->increment('cash', $winnings);
                } elseif ($position === 2 && count($results) > 2) {
                    $winnings = (int) ($prizePool * $thirdShare);
                    $participant->update(['winnings' => $winnings]);
                    $participant->user->increment('cash', $winnings);
                }
            }

            // Update race status
            $race->update([
                'status' => 'finished',
                'finished_at' => now(),
                'winner_id' => $results[0]['participant']->user_id,
            ]);
        });

        return $race->fresh(['participants.player', 'winner']);
    }

    public function leaveRace($player, $raceId)
    {
        $race = Race::findOrFail($raceId);

        if (!$race->isWaiting()) {
            throw new \Exception('Cannot leave a race that has already started');
        }

        $participant = $race->participants()
            ->where('user_id', $player->id)
            ->first();

        if (!$participant) {
            throw new \Exception('You are not in this race');
        }

        DB::transaction(function () use ($race, $player, $participant) {
            // Refund entry fee and bet (with 10% penalty)
            $refundAmount = (int) (($race->entry_fee + $participant->bet_amount) * 0.9);
            $player->increment('cash', $refundAmount);

            // Remove from prize pool
            $race->decrement('prize_pool', $race->entry_fee + $participant->bet_amount);

            // Delete participant
            $participant->delete();
        });

        return $race->fresh();
    }
}
