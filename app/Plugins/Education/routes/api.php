<?php

use App\Plugins\Education\Controllers\CourseController;
use App\Plugins\Education\Controllers\PlayerEducationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Education API Routes
|--------------------------------------------------------------------------
|
| Player-facing education routes
|
*/

Route::middleware('auth:sanctum')->prefix('education')->group(function () {
    // Player course browsing
    Route::get('/courses', [PlayerEducationController::class, 'availableCourses']);
    Route::get('/courses/{course}', [PlayerEducationController::class, 'showCourse']);

    // Player enrollment
    Route::post('/enroll/{course}', [PlayerEducationController::class, 'enroll']);
    Route::post('/cancel', [PlayerEducationController::class, 'cancel']);
    Route::get('/progress', [PlayerEducationController::class, 'progress']);
    Route::get('/history', [PlayerEducationController::class, 'history']);
});

// Public read-only
Route::prefix('education')->group(function () {
    Route::get('/catalog', [PlayerEducationController::class, 'catalog']);
});
