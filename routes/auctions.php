<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuctionsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('auctions')
    ->name('auctions.')
    ->group(function () {
        Route::get('/', [AuctionsController::class, 'index'])->name('index');
        Route::get('/create/{pawn}', [AuctionsController::class, 'create'])->name('create');
        Route::post('/store/{pawn}', [AuctionsController::class, 'store'])->name('store');
        Route::get('/{auction}', [AuctionsController::class, 'show'])->name('show');
        Route::post('/{auction}/cancel', [AuctionsController::class, 'cancel'])->name('cancel');
        Route::post('/{auction}/move-to-sale', [AuctionsController::class, 'moveToSale'])->name('move-to-sale');
    });