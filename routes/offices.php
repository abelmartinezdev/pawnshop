<?php

use App\Http\Controllers\Offices\OfficeController;
use App\Http\Controllers\Offices\OfficeSelectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:offices.manage'])
    ->prefix('offices')
    ->name('offices.')
    ->group(function () {
        Route::get('/select', [OfficeSelectController::class, 'show'])->name('select');
        Route::post('/select', [OfficeSelectController::class, 'store'])->name('select.store');
        Route::get('/', [OfficeController::class, 'index'])->name('index');
        Route::get('/create', [OfficeController::class, 'create'])->name('create');
        Route::post('/', [OfficeController::class, 'store'])->name('store');

        Route::post('/{id}/restore', [OfficeController::class, 'restore'])->name('restore');

        Route::get('/{office}', [OfficeController::class, 'show'])->name('show')->withTrashed();
        Route::get('/{office}/edit', [OfficeController::class, 'edit'])->name('edit')->withTrashed();
        Route::put('/{office}', [OfficeController::class, 'update'])->name('update');
        Route::delete('/{office}', [OfficeController::class, 'destroy'])->name('destroy');
    });