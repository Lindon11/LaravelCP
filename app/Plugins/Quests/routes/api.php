<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Quests\Controllers\QuestsController;

Route::middleware(['auth:sanctum'])->prefix('quests')->group(function () {
    Route::get('/', [QuestsController::class, 'index']);
    Route::get('/active', [QuestsController::class, 'active']);
    Route::get('/completed', [QuestsController::class, 'completed']);
    Route::get('/{id}', [QuestsController::class, 'show']);
    Route::post('/{id}/start', [QuestsController::class, 'start']);
    Route::post('/{id}/abandon', [QuestsController::class, 'abandon']);
    Route::post('/{id}/complete', [QuestsController::class, 'complete']);
});
