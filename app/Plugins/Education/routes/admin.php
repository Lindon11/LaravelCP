<?php

use App\Plugins\Education\Controllers\CourseController;
use App\Plugins\Education\Controllers\EducationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Education Admin Routes
|--------------------------------------------------------------------------
|
| Admin management routes for education
|
*/

Route::middleware(['web', 'auth', 'admin'])->prefix('admin/education')->group(function () {
    // Course management (CRUD)
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // Enrollment management
    Route::get('/enrollments', [EducationController::class, 'allEnrollments']);
    Route::get('/statistics', [EducationController::class, 'statistics']);
});
