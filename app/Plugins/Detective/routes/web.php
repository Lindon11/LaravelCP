<?php

use App\Plugins\Detective\Controllers\DetectiveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/detective', [DetectiveController::class, 'index'])->name('detective.index');
    Route::post('/detective/hire', [DetectiveController::class, 'hire'])->name('detective.hire');
});
