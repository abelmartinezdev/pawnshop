<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsidesController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('asides')
    ->name('asides.')
    ->group(function () {
        Route::get('/', [AsidesController::class, 'index'])->name('index');
        Route::get('/create', [AsidesController::class, 'create'])->name('create');
        Route::post('/', [AsidesController::class, 'store'])->name('store');

        Route::get('/finished', [AsidesController::class, 'finished'])->name('finished');
        Route::get('/cancelled', [AsidesController::class, 'cancelled'])->name('cancelled');

        Route::get('/{aside}', [AsidesController::class, 'show'])->name('show');
        Route::post('/{aside}/payment', [AsidesController::class, 'payment'])->name('payment');
        Route::post('/{aside}/finish', [AsidesController::class, 'finish'])->name('finish');
        Route::post('/{aside}/cancel', [AsidesController::class, 'cancel'])->name('cancel');

        Route::get('/{aside}/ticket', [AsidesController::class, 'ticket'])->name('ticket');
        Route::get('/{aside}/history-ticket', [AsidesController::class, 'historyTicket'])->name('history-ticket');
    });