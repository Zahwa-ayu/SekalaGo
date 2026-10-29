<?php

use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelSearchController;
use App\Http\Controllers\Partner\HotelController;
use App\Http\Controllers\Partner\RoomStockController;
use App\Http\Controllers\Partner\RoomTypeController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Support\Facades\Route;

// Halaman publik: pencarian & detail hotel
Route::get('/', [HotelSearchController::class, 'home'])->name('home');
Route::get('/hotels', [HotelSearchController::class, 'search'])->name('hotels.search');
Route::get('/hotels/{hotel}', [HotelSearchController::class, 'show'])->name('hotels.show');

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

        Route::get('hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
        Route::get('hotels/{hotel}', [AdminHotelController::class, 'show'])->name('hotels.show');
        Route::post('hotels/{hotel}/approve', [AdminHotelController::class, 'approve'])->name('hotels.approve');
        Route::post('hotels/{hotel}/reject', [AdminHotelController::class, 'reject'])->name('hotels.reject');
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