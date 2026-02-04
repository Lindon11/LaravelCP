<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Theft\Controllers\TheftController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Theft routes
    Route::get('/theft', [TheftController::class, 'index'])->name('theft.index');
    Route::post('/theft/{theftType}/attempt', [TheftController::class, 'attempt'])->name('theft.attempt');
    Route::get('/garage', [TheftController::class, 'garage'])->name('theft.garage');
    Route::post('/garage/{garage}/sell', [TheftController::class, 'sell'])->name('theft.sell');
});
