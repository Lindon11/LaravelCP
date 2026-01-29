<?php

use App\Http\Controllers\Api\ChatChannelController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\DirectMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Chat Channels
    Route::get('/channels', [ChatChannelController::class, 'index']);
    Route::post('/channels', [ChatChannelController::class, 'store']);
    Route::get('/channels/{channel}', [ChatChannelController::class, 'show']);
    Route::patch('/channels/{channel}', [ChatChannelController::class, 'update']);
    Route::delete('/channels/{channel}', [ChatChannelController::class, 'destroy']);
    Route::post('/channels/{channel}/members', [ChatChannelController::class, 'addMember']);
    Route::delete('/channels/{channel}/members/{userId}', [ChatChannelController::class, 'removeMember']);

    // Chat Messages
    Route::get('/channels/{channel}/messages', [ChatMessageController::class, 'index']);
    Route::post('/channels/{channel}/messages', [ChatMessageController::class, 'store']);
    Route::patch('/messages/{message}', [ChatMessageController::class, 'update']);
    Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy']);
    Route::post('/messages/{message}/reactions', [ChatMessageController::class, 'addReaction']);
    Route::get('/messages/{message}/reactions', [ChatMessageController::class, 'reactions']);

    // Direct Messages
    Route::get('/direct-messages', [DirectMessageController::class, 'index']);
    Route::get('/direct-messages/{user}', [DirectMessageController::class, 'show']);
    Route::post('/direct-messages', [DirectMessageController::class, 'store']);
    Route::delete('/direct-messages/{message}', [DirectMessageController::class, 'destroy']);
    Route::get('/direct-messages/unread-count', [DirectMessageController::class, 'unreadCount']);
    Route::patch('/direct-messages/{user}/read', [DirectMessageController::class, 'markAsRead']);
});


