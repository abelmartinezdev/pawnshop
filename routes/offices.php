<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Offices\OfficeController;
use App\Http\Controllers\Offices\OfficeSelectController;

Route::middleware(['auth', 'verified'])
    ->prefix('offices')
    ->name('offices.')
    ->group(function () {
        Route::get('/select', [OfficeSelectController::class, 'show'])->name('select');
        Route::post('/select', [OfficeSelectController::class, 'store'])->name('select.store');

        Route::middleware(['ensure_office', 'permission:offices.manage'])->group(function () {
            Route::get('/', [OfficeController::class, 'index'])->name('index');
            Route::get('/create', [OfficeController::class, 'create'])->name('create');
            Route::post('/', [OfficeController::class, 'store'])->name('store');
            Route::get('/{office}/edit', [OfficeController::class, 'edit'])->name('edit');
            Route::put('/{office}', [OfficeController::class, 'update'])->name('update');
            Route::delete('/{office}', [OfficeController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [OfficeController::class, 'restore'])->name('restore');
        });
    });