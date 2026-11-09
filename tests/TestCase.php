<?php

namespace Tests;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hotel;
use App\Models\Partner;
use App\Models\PartnerAgreement;
use App\Models\RoomStock;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\CompanyPosition;
use App\Services\DepositService;
use App\Services\Payment\PaymentGateway;
use Tests\Support\FakePaymentGateway;
use Illuminate\Support\Carbon;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    private static int $sequence = 0;

    /**
     * Pengaman: dipanggil SEBELUM database dikosongkan. Tes menolak berjalan
     * jika database yang dipakai bukan database tes.
     */
    protected function beforeRefreshingDatabase()
    {
        $connection = (string) config('database.default');
        $database   = (string) config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' && ! str_ends_with($database, '_testing')) {
            throw new \RuntimeException(
                "Tes dibatalkan: database \"{$database}\" bukan database tes (nama harus berakhiran _testing)."
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Waktu dibekukan supaya hasil tes tidak bergantung pada jam komputer
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->seed(CurrencySeeder::class);
    }

    /** Tanggal hari ini ditambah $offset hari (jam 00:00). */
    protected function day(int $offset = 0): Carbon
    {
        return today()->addDays($offset);
    }

    // ---------------------------------------------------------------- akun

    protected function makeUser(string $type): User
    {
        return User::create([
            'email'     => 'tes' . (++self::$sequence) . '@sekalago.test',
            'password'  => 'password',
            'user_type' => $type,
        ]);
    }

    protected function makeCustomer(): User
    {
        $user = $this->makeUser('customer');

        Customer::create(['user_id' => $user->id, 'full_name' => 'Budi Tes']);

        return $user;
    }

    /**
     * Mitra terverifikasi. $mou menimpa isi MOU (commission_rate, free_cancel_days, dst).
     * $signed = false: mitra belum punya MOU yang ditandatangani.
     */
    protected function makePartner(array $mou = [], bool $signed = true): Partner
    {
        $user = $this->makeUser('partner');

        $partner = Partner::create([
            'user_id'             => $user->id,
            'company_name'        => 'Mitra Tes ' . self::$sequence,
            'business_license_no' => 'NIB-' . self::$sequence,
            'verification_status' => 'verified',
        ]);

        if ($signed) {
            PartnerAgreement::create(array_merge([
                'partner_id'       => $partner->id,
                'version'          => 1,
                'commission_rate'  => 0.15,
                'payout_schedule'  => 'monthly',
                'free_cancel_days' => 3,
                'content'          => 'MOU tes',
                'status'           => 'signed',
                'sent_at'          => now(),
                'signed_at'        => now(),
                'signed_name'      => 'Mitra Tes',
                'signed_ip'        => '127.0.0.1',
            ], $mou));
        }

        return $partner;
    }

    // --------------------------------------------------------------- hotel

    protected function makeHotel(Partner $partner, array $attributes = []): Hotel
    {
        $hotel = Hotel::create(array_merge([
            'partner_id'  => $partner->id,
            'city'        => 'Yogyakarta',
            'address'     => 'Jl. Tes No. 1',
            'star_rating' => 4,
            'status'      => 'approved',
        ], $attributes));

        $hotel->translations()->create([
            'locale'             => 'id',
            'name'               => 'Hotel Tes ' . $hotel->id,
            'description'        => 'Hotel untuk pengujian.',
            'is_auto_translated' => false,
        ]);

        return $hotel->load('translations');
    }

    /** Tipe kamar dengan stok $quota per malam selama $days hari mulai hari ini. */
    protected function makeRoomType(Hotel $hotel, array $attributes = [], int $quota = 2, int $days = 30): RoomType
    {
        $room = RoomType::create(array_merge([
            'hotel_id'    => $hotel->id,
            'base_price'  => 500000,
            'capacity'    => 2,
            'total_rooms' => $quota,
        ], $attributes));

        $room->translations()->create([
            'locale'             => 'id',
            'name'               => 'Deluxe Tes',
            'description'        => null,
            'is_auto_translated' => false,
        ]);

        for ($i = 0; $i < $days; $i++) {
            RoomStock::create([
                'room_type_id' => $room->id,
                'date'         => $this->day($i)->toDateString(),
                'available'    => $quota,
                'booked'       => 0,
            ]);
        }

        return $room;
    }

    // --------------------------------------------------------------- mobil

    protected function makeVehicle(Partner $partner, array $attributes = []): Vehicle
    {
        $vehicle = Vehicle::create(array_merge([
            'partner_id'         => $partner->id,
            'city'               => 'Yogyakarta',
            'area'               => 'Sleman',
            'address'            => 'Jl. Tes No. 2',
            'type'               => 'mpv',
            'transmission'       => 'manual',
            'fuel_type'          => 'petrol',
            'seats'              => 7,
            'features'           => ['ac'],
            'units'              => 1,
            'price_per_day'      => 450000,
            'min_days'           => 1,
            'driver_mode'        => 'both',
            'driver_fee_per_day' => 250000,
            'status'             => 'approved',
            'source_locale'      => 'id',
        ], $attributes));

        $vehicle->translations()->create([
            'locale'             => 'id',
            'name'               => 'Avanza Tes ' . $vehicle->id,
            'description'        => null,
            'is_auto_translated' => false,
        ]);

        return $vehicle->load('translations');
    }

    // ------------------------------------------------------------ pesanan

    /** Pesan kamar: mulai $from hari dari sekarang, selama $nights malam. */
    protected function bookRoom(User $user, RoomType $room, int $from = 2, int $nights = 2, int $guests = 2): Booking
    {
        return app(BookingService::class)->create(
            $user, $room, $this->day($from), $this->day($from + $nights), $guests, 'Tamu Tes'
        );
    }

    /** Sewa mobil: mulai $from hari dari sekarang, selama $days hari. */
    protected function bookCar(User $user, Vehicle $vehicle, int $from = 2, int $days = 3, bool $withDriver = false, int $passengers = 2): Booking
    {
        return app(BookingService::class)->createForVehicle(
            $user, $vehicle, $this->day($from), $this->day($from + $days), $passengers, 'Penyewa Tes', $withDriver
        );
    }

    protected function pay(Booking $booking, string $reference = 'SBX-TES'): Booking
    {
        app(BookingService::class)->markPaid($booking, $reference);

        return $booking->refresh();
    }

    // ------------------------------------------------------------ gateway

/** Ganti gateway pembayaran dengan versi tiruan yang bisa dihitung dan dikendalikan. */
protected function fakeGateway(): FakePaymentGateway
{
    $fake = new FakePaymentGateway();
    $this->app->instance(PaymentGateway::class, $fake);

    return $fake;
}

// ---------------------------------------------------------- corporate

/** Perusahaan dengan saldo awal $deposit (dicatat lewat buku besar). */
protected function makeCompany(int $deposit = 0, array $attributes = []): Company
{
    $company = Company::create(array_merge([
        'company_name'        => 'PT Tes ' . (++self::$sequence),
        'corporate_code'      => 'TES' . self::$sequence,
        'travel_policy_limit' => 2000000,
        'deposit_balance'     => 0,
    ], $attributes));

    if ($deposit > 0) {
        app(DepositService::class)->credit($company, $deposit, 'topup', 'Saldo awal tes');
    }

    return $company->refresh();
}

/**
 * Karyawan (atau Finance) pada sebuah perusahaan.
 *
 * @return array{0: User, 1: CompanyUser}
 */
protected function makeEmployee(Company $company, string $role = 'employee', array $attributes = []): array
{
    $user = $this->makeUser('corporate');

    $member = CompanyUser::create(array_merge([
        'company_id'      => $company->id,
        'user_id'         => $user->id,
        'full_name'       => 'Karyawan Tes',
        'role_in_company' => $role,
        'department'      => 'Marketing',
    ], $attributes));

    return [$user, $member];
}

/** Pesan kamar sebagai karyawan corporate (dibayar dari saldo kantor). */
protected function bookCorporate(User $user, CompanyUser $member, RoomType $room, int $from = 2, int $nights = 2): Booking
{
    return app(BookingService::class)->create(
        $user, $room, $this->day($from), $this->day($from + $nights), 2, 'Karyawan Tes', $member
    );
}

/** Jabatan dengan batas per pemesanan dan anggaran bulanan (opsional). */
protected function makePosition(Company $company, int $limit, ?int $budget = null): CompanyPosition
{
    return CompanyPosition::forceCreate([
        'company_id'     => $company->id,
        'name'           => 'Jabatan ' . (++self::$sequence),
        'booking_limit'  => $limit,
        'monthly_budget' => $budget,
    ]);
}

/** Sewa mobil sebagai karyawan corporate (dibayar dari saldo kantor). */
protected function bookCorporateCar(User $user, CompanyUser $member, Vehicle $vehicle, int $from = 2, int $days = 2): Booking
{
    return app(BookingService::class)->createForVehicle(
        $user, $vehicle, $this->day($from), $this->day($from + $days), 2, 'Karyawan Tes', false, $member
    );
}
}