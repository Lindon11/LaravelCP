<?php

use App\Plugins\Bullets\Controllers\BulletApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bullets API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('bullets')->group(function () {
    Route::get('/', [BulletApiController::class, 'index']);
    Route::post('/buy', [BulletApiController::class, 'buy']);
});
