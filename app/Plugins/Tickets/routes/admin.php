<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Tickets\Controllers\TicketManagementController;
use App\Plugins\Tickets\Controllers\TicketCategoriesController;

Route::middleware(['auth:sanctum', 'role:admin|moderator'])->prefix('support/tickets')->group(function () {
    // Ticket Management
    Route::get('/', [TicketManagementController::class, 'index']);
    Route::get('/staff/users', [TicketManagementController::class, 'staffUsers']);
    Route::get('/{id}', [TicketManagementController::class, 'show']);
    Route::post('/{id}/reply', [TicketManagementController::class, 'reply']);
    Route::put('/{id}/status', [TicketManagementController::class, 'updateStatus']);
    Route::put('/{id}/priority', [TicketManagementController::class, 'updatePriority']);
    Route::put('/{id}/assign', [TicketManagementController::class, 'assign']);
    Route::delete('/{id}', [TicketManagementController::class, 'destroy']);

    // Ticket Categories Management
    Route::get('/categories/list', [TicketCategoriesController::class, 'index']);
    Route::post('/categories', [TicketCategoriesController::class, 'store']);
    Route::put('/categories/{id}', [TicketCategoriesController::class, 'update']);
    Route::delete('/categories/{id}', [TicketCategoriesController::class, 'destroy']);
});
