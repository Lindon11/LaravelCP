<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Chat\Controllers\ChatController;

Route::middleware(['auth:sanctum'])->prefix('chat')->group(function () {
    Route::get('/channels', [ChatController::class, 'channels']);
    Route::get('/unread', [ChatController::class, 'unreadCount']);
    Route::post('/channels', [ChatController::class, 'createChannel']);
    
    Route::get('/channels/{channel}/messages', [ChatController::class, 'messages']);
    Route::post('/channels/{channel}/messages', [ChatController::class, 'sendMessage']);
    
    Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage']);
    Route::post('/messages/{message}/reactions', [ChatController::class, 'addReaction']);
    Route::delete('/messages/{message}/reactions', [ChatController::class, 'removeReaction']);
});
