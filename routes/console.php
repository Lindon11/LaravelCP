<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule energy refill every minute
Schedule::command('energy:refill')->everyMinute();

// Schedule property income collection every hour
Schedule::command('property:collect-income')->hourly();

// Auto-resolve old errors daily at 3 AM
Schedule::command('errors:auto-resolve --days=7')->dailyAt('03:00');
