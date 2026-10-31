<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Corporate\BookingController as CorporateBookingController;
use App\Http\Controllers\Corporate\DashboardController as CorporateDashboardController;
use App\Http\Controllers\Corporate\EmployeeController;
use App\Http\Controllers\Corporate\PositionController;
use App\Http\Controllers\Corporate\ReportController;
use App\Http\Controllers\HotelSearchController;
use App\Http\Controllers\Partner\AgreementController;
use App\Http\Controllers\Partner\BookingController as PartnerBookingController;
use App\Http\Controllers\Partner\HotelController;
use App\Http\Controllers\Partner\ProfileController;
use App\Http\Controllers\Partner\RoomStockController;
use App\Http\Controllers\Partner\RoomTypeController;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\Admin\SocialMediaController as AdminSocialMediaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
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

    Route::get('/register/partner', [PartnerRegistrationController::class, 'create'])->name('register.partner');
    Route::post('/register/partner', [PartnerRegistrationController::class, 'store'])->name('register.partner.store');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Pemesanan: pelanggan B2C (bayar lewat gateway) dan karyawan corporate (saldo kantor)
Route::middleware(['auth', 'role:customer,corporate'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/pay', [BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('/bookings/{booking}/pay', [BookingController::class, 'processPayment'])->name('bookings.pay.process');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
});

// Admin internal
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'dashboard', ['title' => 'ui.dashboard_admin'])->name('dashboard');

        Route::get('partners', [AdminPartnerController::class, 'index'])->name('partners.index');
        Route::get('partners/{partner}', [AdminPartnerController::class, 'show'])->name('partners.show');
        Route::get('partners/{partner}/agreement/download', [AdminPartnerController::class, 'downloadAgreement'])->name('partners.agreement.download');
        Route::post('partners/{partner}/agreement', [AdminPartnerController::class, 'sendAgreement'])->name('partners.agreement');
        Route::post('partners/{partner}/reject', [AdminPartnerController::class, 'reject'])->name('partners.reject');
        Route::post('partners/{partner}/reopen', [AdminPartnerController::class, 'reopen'])->name('partners.reopen');

        Route::get('hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
        Route::get('hotels/{hotel}', [AdminHotelController::class, 'show'])->name('hotels.show');
        Route::post('hotels/{hotel}/approve', [AdminHotelController::class, 'approve'])->name('hotels.approve');
        Route::post('hotels/{hotel}/reject', [AdminHotelController::class, 'reject'])->name('hotels.reject');

        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::post('bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('companies', [AdminCompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [AdminCompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}', [AdminCompanyController::class, 'show'])->name('companies.show');
        Route::post('companies/{company}/topup', [AdminCompanyController::class, 'topup'])->name('companies.topup');

        // Social media management
        Route::patch('social-media/{socialMedia}/status', [AdminSocialMediaController::class, 'updateStatus'])
            ->name('social-media.status');

        Route::resource('social-media', AdminSocialMediaController::class)
            ->parameters(['social-media' => 'socialMedia'])   // PENTING, lihat catatan
            ->except(['create', 'edit', 'show']);

        // Manajemen user admin internal (role: admin)
        Route::resource('users', AdminUserController::class)
            ->except(['create', 'edit', 'show']);

        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])
            ->name('users.status');
    });

// Mitra / vendor (data sudah diverifikasi admin)
Route::middleware(['auth', 'role:partner', 'partner.verified'])
    ->prefix('partner')
    ->name('partner.')
    ->group(function () {
        // Bisa diakses sebelum MOU ditandatangani
        Route::get('agreement', [AgreementController::class, 'show'])->name('agreement.show');
        Route::get('agreement/download', [AgreementController::class, 'download'])->name('agreement.download');
        Route::post('agreement/sign', [AgreementController::class, 'sign'])->name('agreement.sign');
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Hanya setelah MOU ditandatangani
        Route::middleware('partner.mou')->group(function () {
            Route::view('/', 'dashboard', ['title' => 'ui.dashboard_partner'])->name('dashboard');

            Route::resource('hotels', HotelController::class)->except(['show']);

            Route::resource('hotels.room-types', RoomTypeController::class)
                ->shallow()
                ->except(['show']);

            Route::get('room-types/{room_type}/stocks', [RoomStockController::class, 'edit'])
                ->name('room-types.stocks.edit');
            Route::put('room-types/{room_type}/stocks', [RoomStockController::class, 'update'])
                ->name('room-types.stocks.update');

            Route::get('bookings', [PartnerBookingController::class, 'index'])->name('bookings.index');
        });
    });

// Corporate B2B
Route::middleware(['auth', 'role:corporate'])
    ->prefix('corporate')
    ->name('corporate.')
    ->group(function () {
        Route::get('/', [CorporateDashboardController::class, 'index'])->name('dashboard');

        // Hanya untuk Finance perusahaan
        Route::middleware('corporate.finance')->group(function () {
            Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
            Route::resource('positions', PositionController::class)->except(['show', 'create']);
            Route::put('policy', [PositionController::class, 'updatePolicy'])->name('policy.update');

            Route::get('bookings', [CorporateBookingController::class, 'index'])->name('bookings.index');
            Route::post('bookings/{booking}/approve', [CorporateBookingController::class, 'approve'])->name('bookings.approve');
            Route::post('bookings/{booking}/reject', [CorporateBookingController::class, 'reject'])->name('bookings.reject');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/csv', [ReportController::class, 'csv'])->name('reports.csv');
            Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        });
    });