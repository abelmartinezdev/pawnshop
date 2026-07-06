<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('/', [ProductsController::class, 'index'])->name('index');
        Route::get('/create', [ProductsController::class, 'create'])->name('create');
        Route::post('/', [ProductsController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductsController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductsController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductsController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductsController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [ProductsController::class, 'restore'])->name('restore');
    });