<?php

use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

// Installer Routes (public access)
Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'databaseStore'])->name('database.store');
    Route::get('/settings', [InstallerController::class, 'settings'])->name('settings');
    Route::post('/settings', [InstallerController::class, 'settingsStore'])->name('settings.store');
    Route::get('/install', [InstallerController::class, 'install'])->name('install');
    Route::post('/install/process', [InstallerController::class, 'installProcess'])->name('install.process');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'adminStore'])->name('admin.store');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});

// Serve Vue frontend SPA (catch-all route - must be last)
Route::get('/{any?}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');

// Admin dashboard is handled by Filament at /admin
// All game routes are now in routes/api.php
