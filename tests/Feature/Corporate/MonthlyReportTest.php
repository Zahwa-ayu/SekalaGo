<?php

namespace Tests\Feature\Corporate;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CompanyReport;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
{
    /**
     * Satu perusahaan dengan campuran pesanan (semua dibuat bulan ini):
     *  - 4 terkonfirmasi: Ani 1.000.000 + 500.000 (hotel), Budi 500.000 (hotel) + 900.000 (mobil)
     *  - 1 dibatalkan (Rp1.000.000 dikembalikan), 1 ditolak, 1 menunggu persetujuan
     *
     * @return array<string, mixed>
     */
    private function scenario(): array
    {
        $company  = $this->makeCompany(deposit: 20000000, attributes: ['travel_policy_limit' => 2000000]);
        $position = $this->makePosition($company, 5000000, 4000000);

        [$finance]           = $this->makeEmployee($company, 'admin_finance', ['full_name' => 'Fani Finance', 'department' => 'Finance']);
        [$ani, $aniMember]   = $this->makeEmployee($company, 'employee', ['full_name' => 'Ani A', 'department' => 'Marketing', 'position_id' => $position->id]);
        [$budi, $budiMember] = $this->makeEmployee($company, 'employee', ['full_name' => 'Budi B', 'department' => 'Operations']);

        $partner   = $this->makePartner();
        $hotel     = $this->makeHotel($partner);
        $room      = $this->makeRoomType($hotel, ['base_price' => 500000], quota: 10);
        $expensive = $this->makeRoomType($hotel, ['base_price' => 3000000], quota: 10);
        $vehicle   = $this->makeVehicle($partner, ['units' => 2, 'price_per_day' => 450000]);

        $this->bookCorporate($ani, $aniMember, $room, from: 2, nights: 2);               // 1.000.000
        $this->bookCorporate($ani, $aniMember, $room, from: 5, nights: 1);               //   500.000
        $this->bookCorporate($budi, $budiMember, $room, from: 12, nights: 1);            //   500.000
        $this->bookCorporateCar($budi, $budiMember, $vehicle, from: 3, days: 2);         //   900.000

        $cancelled = $this->bookCorporate($ani, $aniMember, $room, from: 8, nights: 2);  // 1.000.000
        app(BookingService::class)->cancel($cancelled, 'customer');

        $rejected = $this->bookCorporate($budi, $budiMember, $expensive, from: 4, nights: 1);   // melewati limit
        app(BookingService::class)->reject($rejected, $finance, 'Di luar kebijakan');

        $this->bookCorporate($budi, $budiMember, $expensive, from: 6, nights: 1);        // tetap menunggu

        return compact('company', 'finance', 'ani', 'budi');
    }

    private function report(array $scenario, ?Carbon $month = null): array
    {
        return app(CompanyReport::class)->build($scenario['company'], $month ?? now());
    }

    // ------------------------------------------------------------ isi

    public function test_the_summary_totals_count_confirmed_bookings_only(): void
    {
        $summary = $this->report($this->scenario())['summary'];

        $this->assertSame(4, $summary['orders']);
        $this->assertSame(2900000, $summary['total']);
        $this->assertSame(725000, $summary['average']);
        $this->assertSame(6, $summary['nights']);
        $this->assertSame(1, $summary['cancelled']);
        $this->assertSame(1000000, $summary['refunded']);
        $this->assertSame(1, $summary['rejected']);
        $this->assertSame(1, $summary['pending']);
    }

    public function test_the_detail_rows_are_the_confirmed_bookings_and_nothing_else(): void
    {
        $rows = $this->report($this->scenario())['rows'];

        $this->assertCount(4, $rows);
        $this->assertSame(['confirmed'], $rows->pluck('status')->unique()->values()->all());
    }

    public function test_spending_is_split_by_product(): void
    {
        $byProduct = $this->report($this->scenario())['byProduct']->keyBy('type');

        $this->assertSame(3, $byProduct['hotel']['orders']);
        $this->assertSame(2000000, $byProduct['hotel']['total']);
        $this->assertSame(1, $byProduct['car']['orders']);
        $this->assertSame(900000, $byProduct['car']['total']);
    }

    public function test_spending_is_grouped_by_department_biggest_first(): void
    {
        $byDepartment = $this->report($this->scenario())['byDepartment'];

        $this->assertSame(['Marketing', 'Operations'], $byDepartment->pluck('department')->all());
        $this->assertSame([1500000, 1400000], $byDepartment->pluck('total')->all());
        $this->assertSame([2, 2], $byDepartment->pluck('orders')->all());
    }

    public function test_spending_per_employee_shows_budget_usage(): void
    {
        $byEmployee = $this->report($this->scenario())['byEmployee'];

        $this->assertSame(['Ani A', 'Budi B'], $byEmployee->pluck('name')->all());

        $ani = $byEmployee[0];
        $this->assertSame(1500000, $ani['total']);
        $this->assertSame(4000000, $ani['budget']);
        $this->assertSame(38, $ani['used_percent']);   // 37,5% dibulatkan

        $budi = $byEmployee[1];
        $this->assertSame(1400000, $budi['total']);
        $this->assertNull($budi['budget']);
        $this->assertNull($budi['used_percent']);
    }

    public function test_other_companies_never_leak_into_the_report(): void
    {
        $scenario = $this->scenario();

        $other           = $this->makeCompany(deposit: 5000000);
        [$user, $member] = $this->makeEmployee($other);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 5);
        $this->bookCorporate($user, $member, $room, from: 2, nights: 1);

        $this->assertSame(4, $this->report($scenario)['summary']['orders']);
        $this->assertSame(2900000, $this->report($scenario)['summary']['total']);
    }

    // ------------------------------------------------------------ bulan

    public function test_a_booking_belongs_to_the_month_it_was_made_in_wib(): void
    {
        $company         = $this->makeCompany(deposit: 5000000);
        [$user, $member] = $this->makeEmployee($company);
        $room            = $this->makeRoomType($this->makeHotel($this->makePartner()), ['base_price' => 500000], quota: 5);

        $lateOctober   = $this->bookCorporate($user, $member, $room, from: 2, nights: 1);
        $earlyNovember = $this->bookCorporate($user, $member, $room, from: 5, nights: 1);

        Booking::whereKey($lateOctober->id)->update(['created_at' => '2026-10-31 23:30:00']);
        Booking::whereKey($earlyNovember->id)->update(['created_at' => '2026-11-01 00:10:00']);

        $service = app(CompanyReport::class);
        $october = $service->build($company, Carbon::parse('2026-10-15'));
        $november = $service->build($company, Carbon::parse('2026-11-15'));

        $this->assertSame(1, $october['summary']['orders']);
        $this->assertSame($lateOctober->booking_code, $october['rows']->first()->booking_code);
        $this->assertSame(1, $november['summary']['orders']);
        $this->assertSame($earlyNovember->booking_code, $november['rows']->first()->booking_code);
    }

    public function test_a_month_without_bookings_gives_zeros_instead_of_errors(): void
    {
        $report = $this->report($this->scenario(), Carbon::parse('2026-01-15'));

        $this->assertSame(0, $report['summary']['orders']);
        $this->assertSame(0, $report['summary']['total']);
        $this->assertSame(0, $report['summary']['average']);
        $this->assertTrue($report['byProduct']->isEmpty());
        $this->assertTrue($report['rows']->isEmpty());
    }

    // ------------------------------------------------------------- web

    public function test_finance_opens_the_report_page(): void
    {
        $scenario = $this->scenario();

        $this->actingAs($scenario['finance'])
            ->get(route('corporate.reports.index', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee($scenario['company']->company_name);
    }

    public function test_an_invalid_month_is_rejected(): void
    {
        $scenario = $this->scenario();

        $this->actingAs($scenario['finance'])
            ->get(route('corporate.reports.index', ['month' => '2026-13']))
            ->assertSessionHasErrors('month');
    }

    public function test_ordinary_employees_cannot_open_the_report(): void
    {
        $scenario = $this->scenario();

        $this->actingAs($scenario['ani'])
            ->get(route('corporate.reports.index'))
            ->assertForbidden();
    }

    public function test_the_csv_has_one_row_per_confirmed_booking_and_a_matching_total(): void
    {
        $scenario = $this->scenario();

        $response = $this->actingAs($scenario['finance'])
            ->get(route('corporate.reports.csv', ['month' => now()->format('Y-m')]))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);          // BOM agar Excel membaca UTF-8
        $this->assertSame(4, substr_count($csv, 'SG-'));              // satu kode pesanan per baris
        $this->assertStringContainsString('TOTAL;2900000', $csv);
    }

    public function test_the_pdf_downloads(): void
    {
        $scenario = $this->scenario();

        $this->actingAs($scenario['finance'])
            ->get(route('corporate.reports.pdf', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}