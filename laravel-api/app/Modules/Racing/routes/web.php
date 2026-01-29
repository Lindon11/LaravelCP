<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Racing\Controllers\RaceController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('racing')->name('racing.')->group(function () {
    Route::get('/', [RaceController::class, 'index'])->name('index');
    Route::post('/create', [RaceController::class, 'create'])->name('create');
    Route::post('/{race}/join', [RaceController::class, 'join'])->name('join');
    Route::post('/{race}/leave', [RaceController::class, 'leave'])->name('leave');
    Route::post('/{race}/start', [RaceController::class, 'start'])->name('start');
});
