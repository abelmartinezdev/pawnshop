<?php

use Illuminate\Support\Facades\Route;
use App\Actions\Quotations\ContinueQuotationAction;
use App\Actions\Quotations\ShowQuotationAction;

Route::middleware([
    'auth',
    'verified',
    'ensure_office',
    'permission:pawn.manage',
])
    ->prefix('quotations')
    ->name('quotations.')
    ->group(function () {
        Route::get(
            '/',
            ShowQuotationAction::class
        )->name('index');

        Route::post(
            '/continue',
            ContinueQuotationAction::class
        )->name('continue');
    });