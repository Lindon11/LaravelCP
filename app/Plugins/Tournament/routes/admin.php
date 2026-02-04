<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Tournament\Controllers\TournamentManagementController;

/*
|--------------------------------------------------------------------------
| Tournament Plugin Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('tournaments')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // List all tournaments
    Route::get('/', [TournamentManagementController::class, 'index']);

    // Create tournament
    Route::post('/', [TournamentManagementController::class, 'store']);

    // Get tournament details
    Route::get('/{id}', [TournamentManagementController::class, 'show']);

    // Update tournament
    Route::put('/{id}', [TournamentManagementController::class, 'update']);

    // Delete tournament
    Route::delete('/{id}', [TournamentManagementController::class, 'destroy']);

    // Start tournament
    Route::post('/{id}/start', [TournamentManagementController::class, 'start']);

    // Cancel tournament
    Route::post('/{id}/cancel', [TournamentManagementController::class, 'cancel']);

    // Record match result
    Route::post('/{tournamentId}/matches/{matchId}/result', [TournamentManagementController::class, 'recordResult']);
});
