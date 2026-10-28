<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Partner\HotelController;
use App\Http\Controllers\Partner\RoomStockController;
use App\Http\Controllers\Partner\RoomTypeController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::view('/', 'home')->name('home');

// Pilihan bahasa & mata uang (tamu dan user login)
Route::post('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');

// Hanya untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin internal
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'dashboard', ['title' => 'ui.dashboard_admin'])->name('dashboard');
    });

// Mitra / vendor
Route::middleware(['auth', 'role:partner'])
    ->prefix('partner')
    ->name('partner.')
    ->group(function () {
        Route::view('/', 'dashboard', ['title' => 'ui.dashboard_partner'])->name('dashboard');

        Route::resource('hotels', HotelController::class)->except(['show']);

        Route::resource('hotels.room-types', RoomTypeController::class)
            ->shallow()
            ->except(['show']);

        Route::get('room-types/{room_type}/stocks', [RoomStockController::class, 'edit'])
            ->name('room-types.stocks.edit');
        Route::put('room-types/{room_type}/stocks', [RoomStockController::class, 'update'])
            ->name('room-types.stocks.update');
    });

// Corporate B2B
Route::middleware(['auth', 'role:corporate'])
    ->prefix('corporate')
    ->name('corporate.')
    ->group(function () {
        Route::view('/', 'dashboard', ['title' => 'ui.dashboard_corporate'])->name('dashboard');
    });