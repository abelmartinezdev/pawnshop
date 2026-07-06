<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/cash', [ReportsController::class, 'cash'])->name('cash');
        Route::get('/pawns', [ReportsController::class, 'pawns'])->name('pawns');
        Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
        Route::get('/folios', [ReportsController::class, 'folios'])->name('folios');
        Route::get('/generate', [ReportsController::class, 'generate'])->name('generate');
    });