<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('quotations')
    ->name('quotations.')
    ->group(function () {
        Route::get('/', [QuotationsController::class, 'index'])->name('index');
        Route::get('/create', [QuotationsController::class, 'create'])->name('create');
        Route::post('/', [QuotationsController::class, 'store'])->name('store');
        Route::get('/{quotation}', [QuotationsController::class, 'show'])->name('show');
        Route::post('/{quotation}/convert-to-pawn', [QuotationsController::class, 'convertToPawn'])->name('convert-to-pawn');
        Route::post('/{quotation}/cancel', [QuotationsController::class, 'cancel'])->name('cancel');
    });