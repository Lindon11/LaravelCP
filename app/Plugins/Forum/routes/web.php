<?php

use App\Plugins\Forum\Controllers\ForumController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/category/{category}', [ForumController::class, 'category'])->name('forum.category');
    Route::get('/forum/topic/{topic}', [ForumController::class, 'topic'])->name('forum.topic');
    Route::post('/forum/category/{category}/topic', [ForumController::class, 'createTopic'])->name('forum.create-topic');
    Route::post('/forum/topic/{topic}/reply', [ForumController::class, 'reply'])->name('forum.reply');
});
