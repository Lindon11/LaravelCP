<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Announcements\Controllers\AnnouncementsController;

Route::middleware(['auth:sanctum'])->prefix('announcements')->group(function () {
    Route::get('/', [AnnouncementsController::class, 'index']);
    Route::post('/{announcement}/viewed', [AnnouncementsController::class, 'markViewed']);
});
