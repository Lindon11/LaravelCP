<?php

namespace App\Console\Commands;

use App\Core\Models\User;
use Illuminate\Console\Command;

class RefillEnergy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'energy:refill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refill energy for all players based on game settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $refillRate = setting('energy_refill_rate', 5);
        
        // Get all users who are not at max energy
        $users = User::where('energy', '<', \DB::raw('max_energy'))
            ->whereNotNull('last_active')
            ->get();
        
        $refilled = 0;
        
        foreach ($users as $user) {
            $newEnergy = min($user->energy + $refillRate, $user->max_energy);
            
            if ($newEnergy > $user->energy) {
                $user->energy = $newEnergy;
                $user->save();
                $refilled++;
            }
        }
        
        $this->info("Refilled energy for {$refilled} players (+{$refillRate} energy each)");
        
        return Command::SUCCESS;
    }
}
