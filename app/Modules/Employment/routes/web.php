<?php

use App\Modules\Employment\Controllers\EmploymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('employment')->name('employment.')->group(function () {
        Route::get('/', [EmploymentController::class, 'index'])->name('index');
        Route::post('/apply/{position}', [EmploymentController::class, 'apply'])->name('apply');
        Route::post('/work', [EmploymentController::class, 'work'])->name('work');
        Route::post('/quit', [EmploymentController::class, 'quit'])->name('quit');
    });
});
