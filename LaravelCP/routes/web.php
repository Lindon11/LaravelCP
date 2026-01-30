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
    Route::get('/setup-admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/setup-admin', [InstallerController::class, 'adminStore'])->name('admin.store');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});

// Admin Control Panel SPA
Route::prefix('admin')->group(function () {
    Route::get('/{any?}', function () {
        return file_get_contents(public_path('admin/index.html'));
    })->where('any', '.*');
});
