<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Forum\Controllers\ForumController;
use App\Plugins\Forum\Controllers\ForumCategoryController;

// Public read routes
Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index']);
    Route::get('/categories', [ForumController::class, 'categories']);
    Route::get('/category/{category}', [ForumController::class, 'category']);
    Route::get('/topic/{topic}', [ForumController::class, 'topic']);
});

// Protected write routes
Route::middleware(['auth:sanctum'])->prefix('forum')->group(function () {
    Route::post('/category/{category}/topic', [ForumController::class, 'createTopic']);
    Route::post('/topic/{topic}/reply', [ForumController::class, 'reply']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/forum')->group(function () {
    Route::get('/categories', [ForumCategoryController::class, 'index']);
    Route::post('/categories', [ForumCategoryController::class, 'store']);
    Route::get('/categories/{category}', [ForumCategoryController::class, 'show']);
    Route::put('/categories/{category}', [ForumCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [ForumCategoryController::class, 'destroy']);

    Route::get('/topics', [ForumCategoryController::class, 'topics']);
    Route::delete('/topics/{topic}', [ForumCategoryController::class, 'destroyTopic']);
    Route::put('/topics/{topic}/toggle-lock', [ForumCategoryController::class, 'toggleLock']);
    Route::put('/topics/{topic}/toggle-sticky', [ForumCategoryController::class, 'toggleSticky']);

    Route::delete('/posts/{post}', [ForumCategoryController::class, 'destroyPost']);
});
