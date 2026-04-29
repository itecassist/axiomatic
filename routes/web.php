<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CommissionNoteController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('permission:manage commission notes')->group(function () {
        Route::resource('companies', CompanyController::class)->except('show');
        Route::resource('branches', BranchController::class)->except('show');
        Route::resource('employees', EmployeeController::class)->except('show');
    });

    Route::middleware('permission:view commission notes')->group(function () {
        Route::get('/commission-notes', [CommissionNoteController::class, 'index'])->name('commission-notes.index');
        Route::get('/commission-notes/{note}/edit', [CommissionNoteController::class, 'edit'])->name('commission-notes.edit');
        Route::put('/commission-notes/{note}', [CommissionNoteController::class, 'update'])
        ->name('commission-notes.update');
    });

    Route::post('/commission-notes', [CommissionNoteController::class, 'store'])
        ->middleware('permission:manage commission notes')
        ->name('commission-notes.store');

    Route::get('/commission-notes/create', [CommissionNoteController::class, 'create'])
        ->middleware('permission:manage commission notes')
        ->name('commission-notes.create');

    Route::get('/commission-notes/{note}', [CommissionNoteController::class, 'show'])
        ->name('commission-notes.show');
});
