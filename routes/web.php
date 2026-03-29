<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\TenantSelect;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => inertia('Auth/Login'))->name('login');
    Route::post('/login', LoginController::class)->name('login.attempt');
});

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/tenant-select', [TenantSelect::class, 'index'])->name('tenant-select.index');
    Route::post('/tenant-select', [TenantSelect::class, 'store'])->name('tenant-select.store');
});
