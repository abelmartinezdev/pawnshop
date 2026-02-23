<?php

use App\Http\Controllers\PawnsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','verified','ensure_office','permission:pawn.manage'])->group(function () {
    Route::get('/pawns', [PawnsController::class, 'index'])->name('pawns.index');
    Route::get('/pawns/{pawn}', [PawnsController::class, 'show'])->name('pawns.show');

    Route::get('/pawns/{pawn}/pay', [PawnsController::class, 'payForm'])->name('pawns.payForm');
    Route::post('/pawns/{pawn}/pay', [PawnsController::class, 'pay'])->name('pawns.pay');
});