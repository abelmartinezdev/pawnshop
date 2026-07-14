<?php

use App\Http\Controllers\AuctionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('auctions')
    ->name('auctions.')
    ->group(function () {
        Route::get('/', [AuctionsController::class, 'index'])
            ->name('index');

        Route::get('/create/{pawn}', [AuctionsController::class, 'create'])
            ->whereNumber('pawn')
            ->name('create');

        Route::post('/store/{pawn}', [AuctionsController::class, 'store'])
            ->whereNumber('pawn')
            ->name('store');

        Route::get('/{auction}', [AuctionsController::class, 'show'])
            ->whereNumber('auction')
            ->name('show');

        Route::post('/{auction}/cancel', [AuctionsController::class, 'cancel'])
            ->whereNumber('auction')
            ->name('cancel');

        Route::post('/{auction}/move-to-sale', [AuctionsController::class, 'moveToSale'])
            ->whereNumber('auction')
            ->name('move-to-sale');
    });