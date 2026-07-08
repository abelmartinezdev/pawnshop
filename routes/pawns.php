<?php

use App\Http\Controllers\PawnsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('pawns')
    ->name('pawns.')
    ->group(function () {
        Route::get('/', [PawnsController::class, 'index'])->name('index');

        Route::get('/create', [PawnsController::class, 'create'])->name('create');
        Route::post('/', [PawnsController::class, 'store'])->name('store');

        Route::get('/date-expiration/{pawn}', [PawnsController::class, 'editDateExpiration'])
            ->name('date-expiration.edit');

        Route::put('/date-expiration/{pawn}', [PawnsController::class, 'updateDateExpiration'])
            ->name('date-expiration.update');

        Route::get('/print/big-ticket/{pawn}', [PawnsController::class, 'printBigTicket'])
            ->name('print.big-ticket');

        Route::get('/{pawn}/print/countersign', [PawnsController::class, 'printCountersign'])
            ->name('print.countersign');

        Route::get('/{pawn}', [PawnsController::class, 'show'])->name('show');

        Route::get('/{pawn}/pay', [PawnsController::class, 'payForm'])->name('payForm');
        Route::post('/{pawn}/pay', [PawnsController::class, 'pay'])->name('pay');
    });