<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Jail\Controllers\JailController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Jail routes
    Route::get('/jail', [JailController::class, 'index'])->name('jail.index');
    Route::post('/jail/{target}/bustout', [JailController::class, 'bustOut'])->name('jail.bustout');
    Route::post('/jail/bailout', [JailController::class, 'bailOut'])->name('jail.bailout');
});
