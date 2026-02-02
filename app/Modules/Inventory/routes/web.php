<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Inventory\Controllers\InventoryController;

Route::middleware(['auth:sanctum', 'verified'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::post('/buy/{item}', [InventoryController::class, 'buy'])->name('buy');
    Route::post('/sell/{inventoryId}', [InventoryController::class, 'sell'])->name('sell');
    Route::post('/equip/{inventoryId}', [InventoryController::class, 'equip'])->name('equip');
    Route::post('/unequip/{inventoryId}', [InventoryController::class, 'unequip'])->name('unequip');
    Route::post('/use/{inventoryId}', [InventoryController::class, 'use'])->name('use');
});

// Shop routes (separate from inventory)
Route::middleware(['auth:sanctum', 'verified'])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [InventoryController::class, 'shop'])->name('index');
});
