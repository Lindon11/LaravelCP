<?php

use App\Plugins\Bounty\Controllers\BountyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/bounties', [BountyController::class, 'index'])->name('bounties.index');
    Route::post('/bounties/place', [BountyController::class, 'place'])->name('bounties.place');
});
