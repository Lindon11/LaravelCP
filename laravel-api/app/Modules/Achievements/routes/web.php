<?php

use App\Modules\Achievements\Controllers\AchievementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
});
