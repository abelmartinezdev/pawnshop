<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'ensure_office'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');

    Route::redirect('/select-company', '/offices/select')->name('select-company');
    Route::redirect('/select-office', '/offices/select')->name('select-office');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';

require __DIR__.'/access.php';

require __DIR__.'/companies.php';
require __DIR__.'/offices.php';
require __DIR__.'/customers.php';

require __DIR__.'/pawns.php';
require __DIR__.'/quotations.php';

require __DIR__.'/transactions.php';
require __DIR__.'/incomes.php';
require __DIR__.'/expenses.php';
require __DIR__.'/closures.php';

require __DIR__.'/departments.php';
require __DIR__.'/products.php';
require __DIR__.'/auctions.php';

require __DIR__.'/point-of-sales.php';
require __DIR__.'/asides.php';

require __DIR__.'/reports.php';
require __DIR__.'/folios.php';