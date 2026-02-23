<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Account\PasswordController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'ensure_office'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';

require __DIR__.'/access.php';

require __DIR__.'/companies.php';
require __DIR__.'/offices.php';
require __DIR__.'/customers.php';

require __DIR__.'/pawns.php';
require __DIR__.'/transactions.php';
require __DIR__.'/closures.php';