<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Achievements\Controllers\AchievementsController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/achievements', [AchievementsController::class, 'index']);
    Route::get('/achievements/recent', [AchievementsController::class, 'recent']);
});
