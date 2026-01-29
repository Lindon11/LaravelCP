<?php

use App\Modules\Combat\Controllers\CombatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/combat', [CombatController::class, 'index'])->name('combat.index');
    Route::post('/combat/attack', [CombatController::class, 'attack'])->name('combat.attack');
});
