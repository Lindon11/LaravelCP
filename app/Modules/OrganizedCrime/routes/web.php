<?php

use App\Modules\OrganizedCrime\Controllers\OrganizedCrimeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/organized-crimes', [OrganizedCrimeController::class, 'index'])->name('organized-crimes.index');
    Route::post('/organized-crimes/{crime}/attempt', [OrganizedCrimeController::class, 'attempt'])->name('organized-crimes.attempt');
});
