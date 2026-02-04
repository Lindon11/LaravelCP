<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Hospital\Controllers\HospitalController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Hospital routes
    Route::get('/hospital', [HospitalController::class, 'index'])->name('hospital.index');
    Route::post('/hospital/heal', [HospitalController::class, 'heal'])->name('hospital.heal');
    Route::post('/hospital/heal-full', [HospitalController::class, 'healFull'])->name('hospital.healFull');
});
