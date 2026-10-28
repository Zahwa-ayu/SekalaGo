<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'dashboard', ['title' => 'Dashboard Admin'])->name('dashboard');
});

Route::middleware(['auth', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::view('/', 'dashboard', ['title' => 'Portal Mitra'])->name('dashboard');
});

Route::middleware(['auth', 'role:corporate'])->prefix('corporate')->name('corporate.')->group(function () {
    Route::view('/', 'dashboard', ['title' => 'Portal Corporate'])->name('dashboard');
});