<?php

use App\Actions\Pawns\CancelPawnAction;
use App\Actions\Pawns\PrintPawnAnticipatedDateTicketAction;
use App\Actions\Pawns\ShowPawnAnticipatedDateAction;
use App\Actions\Pawns\ShowPawnDiscountAction;
use App\Actions\Pawns\StorePawnDiscountAction;
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

        Route::get('/{pawn}/anticipated-date', ShowPawnAnticipatedDateAction::class)
            ->name('anticipated-date');

        Route::get('/{pawn}/print/anticipated-date', PrintPawnAnticipatedDateTicketAction::class)
            ->name('print.anticipated-date');

        Route::get('/apply-discount/{pawn}', ShowPawnDiscountAction::class)
            ->name('apply-discount');

        Route::post('/apply-discount/{pawn}', StorePawnDiscountAction::class)
            ->name('apply-discount.store');

        Route::put('/{pawn}/cancel', CancelPawnAction::class)
            ->name('cancel');

        Route::get('/{pawn}/pay', [PawnsController::class, 'payForm'])->name('payForm');
        Route::post('/{pawn}/pay', [PawnsController::class, 'pay'])->name('pay');

        Route::get('/{pawn}', [PawnsController::class, 'show'])->name('show');
    });