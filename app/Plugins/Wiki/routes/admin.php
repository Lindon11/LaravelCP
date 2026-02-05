<?php

use App\Plugins\Wiki\Controllers\WikiPageController;
use App\Plugins\Wiki\Controllers\WikiCategoryController;
use App\Plugins\Wiki\Controllers\FaqController;
use App\Plugins\Wiki\Controllers\FaqCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Wiki Admin Routes
|--------------------------------------------------------------------------
|
| Admin management routes for wiki pages, categories, and FAQs
|
*/

Route::middleware(['web', 'auth', 'admin'])->prefix('admin/wiki')->group(function () {
    // Wiki page management
    Route::get('/pages', [WikiPageController::class, 'index']);
    Route::post('/pages', [WikiPageController::class, 'store']);
    Route::get('/pages/{id}', [WikiPageController::class, 'show']);
    Route::put('/pages/{id}', [WikiPageController::class, 'update']);
    Route::delete('/pages/{id}', [WikiPageController::class, 'destroy']);

    // Wiki category management
    Route::get('/categories', [WikiCategoryController::class, 'index']);
    Route::post('/categories', [WikiCategoryController::class, 'store']);
    Route::get('/categories/{id}', [WikiCategoryController::class, 'show']);
    Route::put('/categories/{id}', [WikiCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [WikiCategoryController::class, 'destroy']);

    // FAQ management
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::post('/faqs', [FaqController::class, 'store']);
    Route::get('/faqs/{faq}', [FaqController::class, 'show']);
    Route::put('/faqs/{faq}', [FaqController::class, 'update']);
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);

    // FAQ category management
    Route::get('/faq-categories', [FaqCategoryController::class, 'index']);
    Route::post('/faq-categories', [FaqCategoryController::class, 'store']);
    Route::get('/faq-categories/{id}', [FaqCategoryController::class, 'show']);
    Route::put('/faq-categories/{id}', [FaqCategoryController::class, 'update']);
    Route::delete('/faq-categories/{id}', [FaqCategoryController::class, 'destroy']);
});
