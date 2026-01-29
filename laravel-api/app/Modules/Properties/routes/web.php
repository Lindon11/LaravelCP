<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Properties\Controllers\PropertyController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('properties')->name('properties.')->group(function () {
    Route::get('/', [PropertyController::class, 'index'])->name('index');
    Route::post('/{property}/buy', [PropertyController::class, 'buy'])->name('buy');
    Route::post('/{property}/sell', [PropertyController::class, 'sell'])->name('sell');
    Route::post('/collect-income', [PropertyController::class, 'collectIncome'])->name('collect-income');
});
