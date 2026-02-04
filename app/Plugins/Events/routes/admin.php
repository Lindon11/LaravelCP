<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Events\Controllers\EventManagementController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('events')->group(function () {
    Route::get('/', [EventManagementController::class, 'index']);
    Route::post('/', [EventManagementController::class, 'store']);
    Route::put('/{id}', [EventManagementController::class, 'update']);
    Route::delete('/{id}', [EventManagementController::class, 'destroy']);
    Route::post('/{id}/start', [EventManagementController::class, 'start']);
    Route::post('/{id}/end', [EventManagementController::class, 'end']);
    Route::get('/{id}/participants', [EventManagementController::class, 'participants']);
});
