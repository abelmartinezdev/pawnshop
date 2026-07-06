<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartmentsController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:pawn.manage'])
    ->prefix('departments')
    ->name('departments.')
    ->group(function () {
        Route::get('/', [DepartmentsController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentsController::class, 'create'])->name('create');
        Route::post('/', [DepartmentsController::class, 'store'])->name('store');
        Route::get('/{department}', [DepartmentsController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentsController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentsController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentsController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [DepartmentsController::class, 'restore'])->name('restore');
    });