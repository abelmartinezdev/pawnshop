<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('customers')
    ->name('customers.')
    ->group(function () {
        Route::get('/', [CustomersController::class, 'index'])->name('index');
        Route::get('/create', [CustomersController::class, 'create'])->name('create');
        Route::post('/', [CustomersController::class, 'store'])->name('store');
        Route::get('/{customer}/edit', [CustomersController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomersController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomersController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [CustomersController::class, 'restore'])->name('restore');
    });