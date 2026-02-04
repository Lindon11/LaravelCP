<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Drugs\Controllers\DrugController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('drugs')->name('drugs.')->group(function () {
    Route::get('/', [DrugController::class, 'index'])->name('index');
    Route::post('/buy', [DrugController::class, 'buy'])->name('buy');
    Route::post('/sell', [DrugController::class, 'sell'])->name('sell');
});
