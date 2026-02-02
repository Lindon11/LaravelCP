<?php

use App\Modules\Bullets\Controllers\BulletController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/bullets', [BulletController::class, 'index'])->name('bullets.index');
    Route::post('/bullets/buy', [BulletController::class, 'buy'])->name('bullets.buy');
});
