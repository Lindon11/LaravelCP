<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Market\Controllers\MarketController;

Route::middleware(['auth:sanctum'])->prefix('market')->group(function () {
    // Marketplace listings
    Route::get('/', [MarketController::class, 'index']);
    Route::get('/my-listings', [MarketController::class, 'myListings']);
    Route::get('/{id}', [MarketController::class, 'show']);
    Route::post('/', [MarketController::class, 'create']);
    Route::post('/{id}/buy', [MarketController::class, 'buy']);
    Route::post('/{id}/bid', [MarketController::class, 'bid']);
    Route::delete('/{id}', [MarketController::class, 'cancel']);

    // Player-to-player trades
    Route::get('/trades/list', [MarketController::class, 'trades']);
    Route::post('/trades', [MarketController::class, 'createTrade']);
    Route::post('/trades/{id}/accept', [MarketController::class, 'acceptTrade']);
    Route::post('/trades/{id}/decline', [MarketController::class, 'declineTrade']);
    Route::delete('/trades/{id}', [MarketController::class, 'cancelTrade']);
});
