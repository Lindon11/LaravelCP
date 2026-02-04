<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Stocks\Controllers\StocksController;

Route::middleware(['auth:sanctum'])->prefix('stocks')->group(function () {
    Route::get('/', [StocksController::class, 'index']);
    Route::get('/portfolio', [StocksController::class, 'portfolio']);
    Route::get('/history', [StocksController::class, 'history']);
    Route::get('/{id}', [StocksController::class, 'show']);
    Route::post('/buy', [StocksController::class, 'buy']);
    Route::post('/sell', [StocksController::class, 'sell']);
});
