<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClosuresController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('closures')
    ->name('closures.')
    ->group(function () {
        Route::get('/create', [ClosuresController::class, 'create'])->name('create');
        Route::post('/', [ClosuresController::class, 'store'])->name('store');

        Route::get('/{closure}', [ClosuresController::class, 'show'])->name('show');
        Route::get('/{closure}/ticket', [ClosuresController::class, 'ticket'])->name('ticket');
    });