<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpensesController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('expenses')
    ->name('expenses.')
    ->group(function () {
        Route::get('/', [ExpensesController::class, 'index'])->name('index');
        Route::get('/create', [ExpensesController::class, 'create'])->name('create');
        Route::post('/', [ExpensesController::class, 'store'])->name('store');

        Route::get('/{expense}', [ExpensesController::class, 'show'])->name('show');
        Route::post('/{expense}/cancel', [ExpensesController::class, 'cancel'])->name('cancel');
    });