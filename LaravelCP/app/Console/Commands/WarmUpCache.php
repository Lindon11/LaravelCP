<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmUpCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cache:warm-up';

    /**
     * The console command description.
     */
    protected $description = 'Warm up critical application caches';

    /**
     * Execute the console command.
     */
    public function handle(CacheService $cacheService)
    {
        $this->info('Warming up caches...');
        
        $result = $cacheService->warmUp();
        
        foreach ($result['warmed'] as $cache) {
            $this->info("✓ Cached: {$cache}");
        }
        
        $this->info('Cache warm-up complete!');
        return 0;
    }
}
