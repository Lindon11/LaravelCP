<?php

use App\Plugins\Employment\Controllers\EmploymentApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employment API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('employment')->group(function () {
    Route::get('/companies', [EmploymentApiController::class, 'companies']);
    Route::get('/positions/{company}', [EmploymentApiController::class, 'positions']);
    Route::get('/status', [EmploymentApiController::class, 'status']);
    Route::post('/apply/{position}', [EmploymentApiController::class, 'apply']);
    Route::post('/work', [EmploymentApiController::class, 'work']);
    Route::post('/quit', [EmploymentApiController::class, 'quit']);
    Route::get('/history', [EmploymentApiController::class, 'history']);
});
