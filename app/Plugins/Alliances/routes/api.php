<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Alliances\Controllers\AlliancesController;

Route::middleware(['auth:sanctum'])->prefix('alliances')->group(function () {
    Route::get('/', [AlliancesController::class, 'index']);
    Route::get('/territories', [AlliancesController::class, 'territories']);
    Route::get('/wars', [AlliancesController::class, 'wars']);
    Route::get('/{id}', [AlliancesController::class, 'show']);
    Route::post('/', [AlliancesController::class, 'create']);
    Route::post('/{id}/invite', [AlliancesController::class, 'invite']);
    Route::post('/{id}/leave', [AlliancesController::class, 'leave']);
    Route::post('/{id}/declare-war', [AlliancesController::class, 'declareWar']);
    Route::post('/territories/{territoryId}/attack', [AlliancesController::class, 'attackTerritory']);
});
