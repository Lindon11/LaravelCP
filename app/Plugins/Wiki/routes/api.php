<?php

use App\Plugins\Wiki\Controllers\WikiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Wiki API Routes
|--------------------------------------------------------------------------
|
| Public wiki and FAQ routes (no auth required - read-only)
|
*/

Route::prefix('wiki')->group(function () {
    // Wiki categories
    Route::get('/categories', [WikiController::class, 'categories']);
    Route::get('/categories/{id}', [WikiController::class, 'category']);

    // Wiki pages
    Route::get('/pages', [WikiController::class, 'pages']);
    Route::get('/pages/{identifier}', [WikiController::class, 'page']);

    // Wiki search
    Route::get('/search', [WikiController::class, 'search']);

    // FAQs
    Route::get('/faqs', [WikiController::class, 'faqs']);
    Route::get('/faq-categories', [WikiController::class, 'faqCategories']);
});
