<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Companies\CompanyController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:companies.manage'])
    ->prefix('companies')
    ->name('companies.')
    ->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');

        // restore (tu UI lo estaba haciendo con POST)
        Route::post('/{id}/restore', [CompanyController::class, 'restore'])->name('restore');
    });