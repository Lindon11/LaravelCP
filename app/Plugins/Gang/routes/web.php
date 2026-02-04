<?php

use App\Plugins\Gang\Controllers\GangController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/gangs', [GangController::class, 'index'])->name('gangs.index');
    Route::post('/gangs/create', [GangController::class, 'create'])->name('gangs.create');
    Route::post('/gangs/leave', [GangController::class, 'leave'])->name('gangs.leave');
    Route::post('/gangs/{target}/kick', [GangController::class, 'kick'])->name('gangs.kick');
    Route::post('/gangs/deposit', [GangController::class, 'deposit'])->name('gangs.deposit');
    Route::post('/gangs/withdraw', [GangController::class, 'withdraw'])->name('gangs.withdraw');
});
