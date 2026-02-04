<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Tickets\Controllers\TicketsController;

Route::middleware(['auth:sanctum'])->prefix('tickets')->group(function () {
    Route::get('/', [TicketsController::class, 'index']);
    Route::get('/categories', [TicketsController::class, 'categories']);
    Route::get('/unread', [TicketsController::class, 'unreadCount']);
    Route::post('/', [TicketsController::class, 'store']);
    Route::get('/{id}', [TicketsController::class, 'show']);
    Route::post('/{id}/reply', [TicketsController::class, 'reply']);
    Route::post('/{id}/close', [TicketsController::class, 'close']);
    Route::post('/{id}/reopen', [TicketsController::class, 'reopen']);
});
