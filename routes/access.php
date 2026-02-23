<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Access\UserAccessController;
use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\PermissionController;

Route::middleware(['auth', 'verified', 'ensure_office', 'permission:roles.manage'])
    ->prefix('access')
    ->name('access.')
    ->group(function () {

        // Usuarios (puedes separar permiso users.manage si quieres)
        Route::get('/users', [UserAccessController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserAccessController::class, 'create'])->name('users.create');
        Route::post('/users', [UserAccessController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserAccessController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserAccessController::class, 'update'])->name('users.update');

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Permisos  ✅ (esto arregla tu error MethodNotAllowed por falta de POST)
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });