<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Travel\Controllers\TravelController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('travel')->name('travel.')->group(function () {
    Route::get('/', [TravelController::class, 'index'])->name('index');
    Route::post('/{location}', [TravelController::class, 'travel'])->name('travel');
});
