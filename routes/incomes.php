<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomesController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('incomes')
    ->name('incomes.')
    ->group(function () {
        Route::get('/', [IncomesController::class, 'index'])->name('index');
        Route::get('/create', [IncomesController::class, 'create'])->name('create');
        Route::post('/', [IncomesController::class, 'store'])->name('store');

        Route::get('/{income}', [IncomesController::class, 'show'])->name('show');
        Route::post('/{income}/cancel', [IncomesController::class, 'cancel'])->name('cancel');
    });