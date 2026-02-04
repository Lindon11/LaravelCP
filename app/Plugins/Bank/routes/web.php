<?php

use Illuminate\Support\Facades\Route;
use App\Plugins\Bank\Controllers\BankController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Bank routes
    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/deposit', [BankController::class, 'deposit'])->name('bank.deposit');
    Route::post('/bank/withdraw', [BankController::class, 'withdraw'])->name('bank.withdraw');
    Route::post('/bank/transfer', [BankController::class, 'transfer'])->name('bank.transfer');
});
