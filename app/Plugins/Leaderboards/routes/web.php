<?php

use App\Plugins\Leaderboards\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/leaderboards', [LeaderboardController::class, 'index'])->name('leaderboards.index');
});
