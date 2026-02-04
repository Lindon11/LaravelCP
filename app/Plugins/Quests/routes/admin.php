<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Quests\Controllers\QuestManagementController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('quests')->group(function () {
    Route::get('/', [QuestManagementController::class, 'index']);
    Route::post('/', [QuestManagementController::class, 'store']);
    Route::put('/{id}', [QuestManagementController::class, 'update']);
    Route::delete('/{id}', [QuestManagementController::class, 'destroy']);
});
