<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Gym\Controllers\GymController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Gym routes
    Route::get('/gym', [GymController::class, 'index'])->name('gym.index');
    Route::post('/gym/train', [GymController::class, 'train'])->name('gym.train');
});
