<?php

use App\Http\Controllers\Transactions\TransactionsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('transactions', [TransactionsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('transactions');

Route::post('transactions', [TransactionsController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('transactions.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
