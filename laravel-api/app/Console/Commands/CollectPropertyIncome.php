<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Console\Command;

class CollectPropertyIncome extends Command
{
    protected $signature = 'property:collect-income';
    protected $description = 'Automatically collect property income for all players every hour';

    public function __construct(
        private PropertyService $propertyService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $players = User::whereHas('properties')->get();
        $totalCollected = 0;
        $playersProcessed = 0;

        foreach ($players as $player) {
            try {
                $result = $this->propertyService->collectIncome($player);
                if ($result['income'] > 0) {
                    $totalCollected += $result['income'];
                    $playersProcessed++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to collect income for player {$player->id}: {$e->getMessage()}");
            }
        }

        $this->info("Collected \${$totalCollected} for {$playersProcessed} players.");
        return 0;
    }
}
