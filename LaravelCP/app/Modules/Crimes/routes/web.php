<?php

use App\Modules\Crimes\Controllers\CrimeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('crimes')->name('crimes.')->group(function () {
        Route::get('/', [CrimeController::class, 'index'])->name('index');
        Route::post('/{crime}/attempt', [CrimeController::class, 'attempt'])->name('attempt');
        Route::get('/stats', [CrimeController::class, 'stats'])->name('stats');
    });
});
