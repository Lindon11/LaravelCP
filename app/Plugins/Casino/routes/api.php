<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Casino\Controllers\CasinoController;

Route::middleware(['auth:sanctum'])->prefix('casino')->group(function () {
    Route::get('/games', [CasinoController::class, 'games']);
    Route::post('/slots', [CasinoController::class, 'playSlots']);
    Route::post('/roulette', [CasinoController::class, 'playRoulette']);
    Route::post('/dice', [CasinoController::class, 'playDice']);
    Route::get('/stats', [CasinoController::class, 'stats']);
    Route::get('/history', [CasinoController::class, 'history']);
    Route::post('/lottery/buy', [CasinoController::class, 'buyLotteryTicket']);
});
