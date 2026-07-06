<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoliosController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:cash.manage'])
    ->prefix('folios')
    ->name('folios.')
    ->group(function () {
        Route::get('/', [FoliosController::class, 'index'])->name('index');
        Route::get('/reports', [FoliosController::class, 'reports'])->name('reports');
        Route::get('/reports/render', [FoliosController::class, 'renderReports'])->name('reports.render');
    });