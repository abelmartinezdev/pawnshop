<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('transactions')
    ->name('transactions.')
    ->group(function () {
        // opcional si haces listado
        Route::get('/', [TransactionsController::class, 'index'])->name('index');

        Route::get('/{transaction}', [TransactionsController::class, 'show'])->name('show');
        Route::post('/{transaction}/cancel', [TransactionsController::class, 'cancel'])->name('cancel');
    });