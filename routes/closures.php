<?php

use App\Http\Controllers\ClosuresController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('closures')
    ->name('closures.')
    ->group(function () {
        Route::get('/', [ClosuresController::class, 'index'])->name('index');
        Route::get('/create', [ClosuresController::class, 'create'])->name('create');
        Route::post('/', [ClosuresController::class, 'store'])->name('store');
        Route::get('/{closure}', [ClosuresController::class, 'show'])->name('show');
    });