<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PointOfSalesController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('point-of-sales')
    ->name('point-of-sales.')
    ->group(function () {
        Route::get('/', [PointOfSalesController::class, 'index'])->name('index');

        Route::get('/inventory', [PointOfSalesController::class, 'inventory'])->name('inventory');
        Route::get('/inventory/deleted', [PointOfSalesController::class, 'deletedInventory'])->name('inventory.deleted');
        Route::post('/inventory/{saleItem}/restore', [PointOfSalesController::class, 'restoreInventory'])->name('inventory.restore');
        Route::delete('/inventory/{saleItem}', [PointOfSalesController::class, 'destroyInventory'])->name('inventory.destroy');

        Route::get('/sales', [PointOfSalesController::class, 'sales'])->name('sales');
        Route::post('/sales', [PointOfSalesController::class, 'storeSale'])->name('sales.store');
        Route::get('/sales/{sale}', [PointOfSalesController::class, 'showSale'])->name('sales.show');
        Route::get('/sales/{sale}/ticket', [PointOfSalesController::class, 'ticket'])->name('sales.ticket');
        Route::post('/sales/{sale}/cancel', [PointOfSalesController::class, 'cancelSale'])->name('sales.cancel');

        Route::get('/reports', [PointOfSalesController::class, 'reports'])->name('reports');
        Route::get('/reports/render', [PointOfSalesController::class, 'renderReports'])->name('reports.render');

        Route::get('/{auction}/move', [PointOfSalesController::class, 'move'])->name('move');
        Route::post('/{auction}/move', [PointOfSalesController::class, 'moveStore'])->name('move.store');

        Route::get('/{saleItem}/suggestions', [PointOfSalesController::class, 'suggestions'])->name('suggestions');
        Route::post('/{saleItem}/suggestions', [PointOfSalesController::class, 'suggestionStore'])->name('suggestions.store');
    });