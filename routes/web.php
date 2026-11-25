<?php

use App\Http\Controllers\Admin\ActivityLogController;
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
use App\Http\Controllers\Admin\CurrencyController as AdminCurrencyController;
use App\Http\Controllers\HotelSearchController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Partner\AgreementController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Partner\BookingController as PartnerBookingController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Partner\PayoutController as PartnerPayoutController;
use App\Http\Controllers\Partner\ReportController as PartnerReportController;
use App\Http\Controllers\Partner\HotelController;
use App\Http\Controllers\Admin\ContactInfoController;
use App\Http\Controllers\Partner\ProfileController;
use App\Http\Controllers\Partner\RoomStockController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\CalendarPriceController;
use App\Http\Controllers\CorporateRegistrationController;
use App\Http\Controllers\Admin\FaqAdminController;
use App\Http\Controllers\Partner\RoomTypeController;
use App\Http\Controllers\PartnerRegistrationController;
use App\Http\Controllers\Corporate\AuditController as CorporateAuditController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\Admin\SocialMediaController as AdminSocialMediaController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\PhotoController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\Partner\BlackoutController;
use App\Http\Controllers\Partner\VehicleController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Partner\RentalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CarBookingController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\BookingExtensionController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\PlatformBankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\TopupController as AdminTopupController;
use App\Http\Controllers\Corporate\TopupController as CorporateTopupController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\Partner\IssueController as PartnerIssueController;
use App\Http\Controllers\Partner\ReviewController as PartnerReviewController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\Partner\StayController as PartnerStayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarSearchController;

// Halaman publik: pencarian & detail hotel
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/hotels', [HotelSearchController::class, 'search'])->name('hotels.search');
Route::get('/hotels/{hotel}', [HotelSearchController::class, 'show'])->name('hotels.show');
Route::get('/cars', [CarSearchController::class, 'home'])->name('cars.home');
Route::get('/cars/search', [CarSearchController::class, 'legacySearch'])->name('cars.search');
Route::get('/cars/{vehicle}', [CarSearchController::class, 'show'])->name('cars.show');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('pages.show');
// Halaman hukum, Tentang, dan Kontak
// Pilihan bahasa & mata uang (tamu dan user login)
Route::post('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
Route::get('/calendar/prices', [CalendarPriceController::class, 'index'])->middleware('throttle:60,1')->name('calendar.prices');
Route::post('/midtrans/notification', MidtransWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class])
    ->name('midtrans.notification');

// Lupa dan reset password (tamu)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

// Hanya untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/register/partner', [PartnerRegistrationController::class, 'create'])->name('register.partner');
    Route::post('/register/partner', [PartnerRegistrationController::class, 'store'])->name('register.partner.store');

    Route::get('/register/corporate', [CorporateRegistrationController::class, 'create'])->name('register.corporate');
    Route::post('/register/corporate', [CorporateRegistrationController::class, 'store'])->middleware('throttle:6,1')->name('register.corporate.store');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Pengaturan akun & ganti password (semua peran yang sudah login)
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'show'])->name('account.settings');
    Route::get('/account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/edit', [AccountController::class, 'update'])->name('account.update');
    Route::get('/account/preferences', [AccountController::class, 'preferences'])->name('account.preferences');

    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Pengaturan notifikasi (tab di Pengaturan Akun)
    Route::get('/account/notifications', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::put('/account/notifications', [AccountController::class, 'updateNotifications'])->name('account.notifications.update');

    // Bukti laporan masalah: bisa dibuka pelapor, mitra, dan admin
    Route::get('/issues/{issue}/evidence', [IssueController::class, 'evidence'])->name('issues.evidence');
});

// Pemesanan: pelanggan B2C (bayar lewat gateway) dan karyawan corporate (saldo kantor)
Route::middleware(['auth', 'role:customer,corporate'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

    Route::get('/bookings/{booking}/pay', [BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('/bookings/{booking}/pay', [BookingController::class, 'processPayment'])->name('bookings.pay.process');
    Route::post('/bookings/{booking}/pay/snap', [MidtransController::class, 'snap'])->middleware('throttle:20,1')->name('bookings.pay.snap');
    Route::get('/bookings/{booking}/pay/status', [MidtransController::class, 'status'])->middleware('throttle:60,1')->name('bookings.pay.status');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
    Route::get('/bookings/{booking}/extend', [BookingExtensionController::class, 'create'])->name('bookings.extend');
    Route::post('/bookings/{booking}/extend', [BookingExtensionController::class, 'store'])->name('bookings.extend.store');

    Route::get('/car-bookings/create', [CarBookingController::class, 'create'])->name('car-bookings.create');
    Route::post('/car-bookings', [CarBookingController::class, 'store'])->name('car-bookings.store');

    Route::get('/bookings/{booking}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/bookings/{booking}/issue', [IssueController::class, 'create'])->name('issues.create');
    Route::post('/bookings/{booking}/issue', [IssueController::class, 'store'])->name('issues.store');
    Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
});

// Admin internal
Route::middleware(['auth', 'role:admin', 'admin.ability'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Analytics & Revenue
        Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/csv', [AdminAnalyticsController::class, 'csv'])->name('analytics.csv');

        // PENTING: partners/create harus di atas partners/{partner}, kalau tidak "create" dianggap ID mitra (404)
        Route::get('partners', [AdminPartnerController::class, 'index'])->name('partners.index');
        Route::get('partners/create', [AdminPartnerController::class, 'create'])->name('partners.create');
        Route::post('partners', [AdminPartnerController::class, 'store'])->name('partners.store');
        Route::get('partners/{partner}', [AdminPartnerController::class, 'show'])->name('partners.show');
        Route::get('partners/{partner}/agreement/download', [AdminPartnerController::class, 'downloadAgreement'])->name('partners.agreement.download');
        Route::post('partners/{partner}/agreement', [AdminPartnerController::class, 'sendAgreement'])->name('partners.agreement');
        Route::post('partners/{partner}/reject', [AdminPartnerController::class, 'reject'])->name('partners.reject');
        Route::post('partners/{partner}/reopen', [AdminPartnerController::class, 'reopen'])->name('partners.reopen');

        Route::get('partners/{partner}/documents/{type}', [AdminPartnerController::class, 'document'])
            ->where('type', 'tax|license|supporting')->name('partners.document');

        Route::get('hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
        Route::get('hotels/{hotel}', [AdminHotelController::class, 'show'])->name('hotels.show');
        Route::post('hotels/{hotel}/approve', [AdminHotelController::class, 'approve'])->name('hotels.approve');
        Route::post('hotels/{hotel}/reject', [AdminHotelController::class, 'reject'])->name('hotels.reject');

        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('companies', [AdminCompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [AdminCompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}', [AdminCompanyController::class, 'show'])->name('companies.show');
        Route::post('companies/{company}/topup', [AdminCompanyController::class, 'topup'])->name('companies.topup');
        Route::post('companies/{company}/documents', [AdminCompanyController::class, 'uploadDocuments'])->name('companies.documents.store');
        Route::post('companies/{company}/verify', [AdminCompanyController::class, 'verify'])->name('companies.verify');
        Route::post('companies/{company}/reject', [AdminCompanyController::class, 'reject'])->name('companies.reject');
        Route::post('companies/{company}/reopen', [AdminCompanyController::class, 'reopen'])->name('companies.reopen');
        Route::get('companies/{company}/documents/{type}', [AdminCompanyController::class, 'document'])->where('type', 'tax|license')->name('companies.document');

        Route::get('companies/{company}/audit', [CorporateAuditController::class, 'index'])->name('companies.audit');
        Route::get('companies/{company}/audit/csv', [CorporateAuditController::class, 'csv'])->name('companies.audit.csv');
        Route::get('companies/{company}/audit/pdf', [CorporateAuditController::class, 'pdf'])->name('companies.audit.pdf');

        // Pencairan dana mitra
        Route::get('payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('payouts', [AdminPayoutController::class, 'store'])->name('payouts.store');
        Route::post('payouts/generate', [AdminPayoutController::class, 'generate'])->name('payouts.generate');
        Route::get('payouts/{payout}', [AdminPayoutController::class, 'show'])->name('payouts.show');
        Route::get('payouts/{payout}/csv', [AdminPayoutController::class, 'csv'])->name('payouts.csv');
        Route::post('payouts/{payout}/paid', [AdminPayoutController::class, 'markPaid'])->name('payouts.paid');
        Route::post('payouts/{payout}/cancel', [AdminPayoutController::class, 'cancel'])->name('payouts.cancel');
        Route::get('payouts/{payout}/proof', [AdminPayoutController::class, 'proof'])->name('payouts.proof');

        // Pembayaran & refund manual
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/refunded', [AdminPaymentController::class, 'manualRefunded'])->name('payments.refunded');
        Route::post('payments/{payment}/fee', [AdminPaymentController::class, 'correctFee'])->name('payments.fee');

        // Master data: mata uang dan kurs
        Route::get('currencies', [AdminCurrencyController::class, 'index'])->name('currencies.index');
        Route::post('currencies', [AdminCurrencyController::class, 'store'])->name('currencies.store');
        Route::get('currencies/{currency}/edit', [AdminCurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('currencies/{currency}', [AdminCurrencyController::class, 'update'])->name('currencies.update');
        Route::put('currencies/{currency}/rate', [AdminCurrencyController::class, 'updateRate'])->name('currencies.rate');
        Route::delete('currencies/{currency}', [AdminCurrencyController::class, 'destroy'])->name('currencies.destroy');

        // Social media management
        Route::patch('social-media/{socialMedia}/status', [AdminSocialMediaController::class, 'updateStatus'])
            ->name('social-media.status');

        Route::resource('social-media', AdminSocialMediaController::class)
            ->parameters(['social-media' => 'socialMedia'])   // PENTING, lihat catatan
            ->except(['create', 'edit', 'show']);

        // Banner halaman (foto hero Beranda, Hotel, Rental Mobil)
        Route::get('banners', [AdminBannerController::class, 'index'])->name('banners.index');
        Route::post('banners/{key}', [AdminBannerController::class, 'update'])->name('banners.update')->where('key', 'home|hotels|cars');
        Route::delete('banners/{key}', [AdminBannerController::class, 'reset'])->name('banners.reset')->where('key', 'home|hotels|cars');

        // Manajemen user admin internal (role: admin)
        Route::resource('users', AdminUserController::class)
            ->except(['create', 'edit', 'show']);

        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])
            ->name('users.status');

        // Tim internal SekalaGo
        Route::get('staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::post('staff', [AdminStaffController::class, 'store'])->name('staff.store');
        Route::patch('staff/{user}', [AdminStaffController::class, 'update'])->name('staff.update');

        // Master data: kota
        Route::resource('cities', AdminCityController::class)->except(['create', 'show']);

        // Log aktivitas
        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

        // rental cars
        Route::get('vehicles', [AdminVehicleController::class, 'index'])->name('vehicles.index');
        Route::get('vehicles/{vehicle}', [AdminVehicleController::class, 'show'])->name('vehicles.show');
        Route::post('vehicles/{vehicle}/approve', [AdminVehicleController::class, 'approve'])->name('vehicles.approve');
        Route::post('vehicles/{vehicle}/reject', [AdminVehicleController::class, 'reject'])->name('vehicles.reject');

        // Manajemen FAQ (dinamis, tampil di halaman publik)
        Route::resource('faqs', FaqAdminController::class)->except(['show']);

        // Konten: halaman statis
        Route::resource('pages', PageAdminController::class)->except(['show']);

        // Ulasan dan laporan masalah
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('reviews/{review}/hide', [AdminReviewController::class, 'hide'])->name('reviews.hide');
        Route::post('reviews/{review}/unhide', [AdminReviewController::class, 'unhide'])->name('reviews.unhide');
        Route::get('issues', [AdminIssueController::class, 'index'])->name('issues.index');
        Route::post('issues/{issue}', [AdminIssueController::class, 'update'])->name('issues.update');

        // Master data: fasilitas hotel dan kamar
        Route::resource('facilities', AdminFacilityController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('facilities/{facility}/status', [AdminFacilityController::class, 'status'])->name('facilities.status');

        // Rekening bisnis SekalaGo dan permintaan top-up saldo
        Route::resource('bank-accounts', AdminBankAccountController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::get('topups', [AdminTopupController::class, 'index'])->name('topups.index');
        Route::post('topups/{topup}/approve', [AdminTopupController::class, 'approve'])->name('topups.approve');
        Route::post('topups/{topup}/reject', [AdminTopupController::class, 'reject'])->name('topups.reject');
        Route::get('topups/{topup}/proof', [AdminTopupController::class, 'proof'])->name('topups.proof');

        // Info kontak
        Route::prefix('contact-info')->name('contact-info.')->controller(ContactInfoController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('{contactInfo}', 'update')->name('update');
            Route::patch('{contactInfo}/status', 'updateStatus')->name('status');
            Route::delete('{contactInfo}', 'destroy')->name('destroy');
        });
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
            Route::get('/', [PartnerDashboardController::class, 'index'])->name('dashboard');

            Route::resource('hotels', HotelController::class)->except(['show']);

            Route::resource('hotels.room-types', RoomTypeController::class)
                ->shallow()
                ->except(['show']);

            Route::get('payouts', [PartnerPayoutController::class, 'index'])->name('payouts.index');
            Route::get('payouts/{payout}/proof', [PartnerPayoutController::class, 'proof'])->name('payouts.proof');

            Route::get('room-types/{room_type}/stocks', [RoomStockController::class, 'edit'])
                ->name('room-types.stocks.edit');
            Route::put('room-types/{room_type}/stocks', [RoomStockController::class, 'update'])
                ->name('room-types.stocks.update');

            Route::get('bookings', [PartnerBookingController::class, 'index'])->name('bookings.index');
            Route::get('rentals', [RentalController::class, 'index'])->name('rentals.index');
            Route::get('bookings/{booking}', [PartnerBookingController::class, 'show'])->name('bookings.show');
            Route::post('rentals/{booking}/pickup', [RentalController::class, 'markPickedUp'])->name('rentals.pickup');
            Route::post('rentals/{booking}/return', [RentalController::class, 'markReturned'])->name('rentals.return');
            Route::post('rentals/{booking}/cancel', [RentalController::class, 'cancelAtRisk'])->name('rentals.cancel');

            // Foto hotel dan tipe kamar
            Route::get('hotels/{hotel}/photos', [PhotoController::class, 'hotelIndex'])->name('hotels.photos.index');
            Route::post('hotels/{hotel}/photos', [PhotoController::class, 'hotelStore'])->name('hotels.photos.store');
            Route::get('room-types/{room_type}/photos', [PhotoController::class, 'roomTypeIndex'])->name('room-types.photos.index');
            Route::post('room-types/{room_type}/photos', [PhotoController::class, 'roomTypeStore'])->name('room-types.photos.store');
            Route::post('photos/{photo}/cover', [PhotoController::class, 'cover'])->name('photos.cover');
            Route::delete('photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

            Route::get('reports', [PartnerReportController::class, 'index'])->name('reports.index');
            Route::get('reports/csv', [PartnerReportController::class, 'csv'])->name('reports.csv');
            Route::get('reports/pdf', [PartnerReportController::class, 'pdf'])->name('reports.pdf');

            // rental cars
            Route::resource('vehicles', VehicleController::class)->except(['show']);

            Route::get('vehicles/{vehicle}/photos', [PhotoController::class, 'vehicleIndex'])->name('vehicles.photos.index');
            Route::post('vehicles/{vehicle}/photos', [PhotoController::class, 'vehicleStore'])->name('vehicles.photos.store');

            Route::get('vehicles/{vehicle}/blackouts', [BlackoutController::class, 'index'])->name('vehicles.blackouts.index');
            Route::post('vehicles/{vehicle}/blackouts', [BlackoutController::class, 'store'])->name('vehicles.blackouts.store');
            Route::delete('blackouts/{blackout}', [BlackoutController::class, 'destroy'])->name('blackouts.destroy');

            Route::get('stays', [PartnerStayController::class, 'index'])->name('stays.index');
            Route::post('stays/{booking}/arrive', [PartnerStayController::class, 'arrive'])->name('stays.arrive');
            Route::post('stays/{booking}/no-show', [PartnerStayController::class, 'noShow'])->name('stays.noshow');
            Route::post('stays/{booking}/depart', [PartnerStayController::class, 'depart'])->name('stays.depart');

            // Ulasan dan laporan masalah dari pelanggan
            Route::get('reviews', [PartnerReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{review}/reply', [PartnerReviewController::class, 'reply'])->name('reviews.reply');
            Route::get('issues', [PartnerIssueController::class, 'index'])->name('issues.index');
            Route::post('issues/{issue}/respond', [PartnerIssueController::class, 'respond'])->name('issues.respond');
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
            Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
            Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
            Route::resource('positions', PositionController::class)->except(['show', 'create']);
            Route::put('policy', [PositionController::class, 'updatePolicy'])->name('policy.update');

            Route::get('bookings', [CorporateBookingController::class, 'index'])->name('bookings.index');
            Route::get('bookings/{booking}', [CorporateBookingController::class, 'show'])->name('bookings.show');
            Route::post('bookings/{booking}/approve', [CorporateBookingController::class, 'approve'])->name('bookings.approve');
            Route::post('bookings/{booking}/reject', [CorporateBookingController::class, 'reject'])->name('bookings.reject');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/csv', [ReportController::class, 'csv'])->name('reports.csv');
            Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
            Route::get('topups', [CorporateTopupController::class, 'index'])->name('topups.index');

            Route::post('topups', [CorporateTopupController::class, 'store'])->name('topups.store');
            Route::post('topups/{topup}/cancel', [CorporateTopupController::class, 'cancel'])->name('topups.cancel');
            Route::get('topups/{topup}/proof', [CorporateTopupController::class, 'proof'])->name('topups.proof');

            Route::get('audit', [CorporateAuditController::class, 'index'])->name('audit.index');
            Route::get('audit/csv', [CorporateAuditController::class, 'csv'])->name('audit.csv');
            Route::get('audit/pdf', [CorporateAuditController::class, 'pdf'])->name('audit.pdf');
        });
    });