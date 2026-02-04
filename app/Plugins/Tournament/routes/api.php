<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Tournament\Controllers\TournamentController;

/*
|--------------------------------------------------------------------------
| Tournament Plugin API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('tournaments')->middleware(['auth:sanctum'])->group(function () {
    // List tournaments
    Route::get('/', [TournamentController::class, 'index']);

    // Get tournament details
    Route::get('/{id}', [TournamentController::class, 'show']);

    // Get tournament bracket
    Route::get('/{id}/bracket', [TournamentController::class, 'bracket']);

    // Get tournament leaderboard
    Route::get('/{id}/leaderboard', [TournamentController::class, 'leaderboard']);

    // Get match details
    Route::get('/{tournamentId}/matches/{matchId}', [TournamentController::class, 'match']);

    // Register for tournament
    Route::post('/{id}/register', [TournamentController::class, 'register']);

    // Withdraw from tournament
    Route::post('/{id}/withdraw', [TournamentController::class, 'withdraw']);
});
