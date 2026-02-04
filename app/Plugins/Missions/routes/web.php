<?php

use App\Plugins\Missions\Controllers\MissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/missions', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missions/start', [MissionController::class, 'start'])->name('missions.start');
});
