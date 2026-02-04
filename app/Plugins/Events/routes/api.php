<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Events\Controllers\EventsController;

Route::middleware(['auth:sanctum'])->prefix('events')->group(function () {
    Route::get('/', [EventsController::class, 'index']);
    Route::get('/{id}', [EventsController::class, 'show']);
    Route::post('/{id}/join', [EventsController::class, 'join']);
    Route::post('/{id}/leave', [EventsController::class, 'leave']);
    Route::get('/{id}/leaderboard', [EventsController::class, 'leaderboard']);
    Route::post('/{id}/claim-reward', [EventsController::class, 'claimReward']);
});
