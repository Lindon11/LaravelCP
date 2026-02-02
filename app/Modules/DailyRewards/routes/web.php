<?php

use App\Modules\DailyRewards\Controllers\DailyRewardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/daily-rewards/claim', [DailyRewardController::class, 'claim'])->name('daily-rewards.claim');
});
