<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Messaging\Controllers\MessagingController;

Route::middleware(['auth:sanctum'])->prefix('messages')->group(function () {
    Route::get('/inbox', [MessagingController::class, 'inbox']);
    Route::get('/sent', [MessagingController::class, 'sent']);
    Route::get('/unread-count', [MessagingController::class, 'unreadCount']);
    Route::get('/conversation/{userId}', [MessagingController::class, 'conversation']);
    Route::get('/{id}', [MessagingController::class, 'show']);
    Route::post('/', [MessagingController::class, 'send']);
    Route::delete('/{id}', [MessagingController::class, 'delete']);
    Route::post('/{id}/read', [MessagingController::class, 'markRead']);
    Route::post('/mark-all-read', [MessagingController::class, 'markAllRead']);
});
