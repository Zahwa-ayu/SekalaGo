<?php

namespace Tests\Feature\Rental;

use App\Services\CompanyReport;
use App\Services\RentalService;
use Tests\TestCase;

class LateFeeReportTest extends TestCase
{
    /** Sewa corporate Rp450.000 yang dikembalikan 2,5 jam terlambat (denda Rp135.000). */
    private function lateRental(?int $waivedTo = null): array
    {
        $company         = $this->makeCompany(deposit: 5000000);
        [$finance]       = $this->makeEmployee($company, 'admin_finance');
        [$user, $member] = $this->makeEmployee($company);
        $vehicle         = $this->makeVehicle($this->makePartner(), ['units' => 2, 'price_per_day' => 450000]);

        $booking = $this->bookCorporateCar($user, $member, $vehicle, from: 0, days: 1);
        app(RentalService::class)->markPickedUp($booking);

        $this->travelTo($booking->fresh()->due_at->copy()->addMinutes(150));
        app(RentalService::class)->markReturned($booking, $waivedTo);

        return [$company, $finance, $booking->fresh()];
    }

    public function test_the_report_lists_late_fees_separately_from_spending(): void
    {
        [$company] = $this->lateRental();

        $summary = app(CompanyReport::class)->build($company, now())['summary'];

        $this->assertSame(450000, $summary['total']);       // belanja tidak termasuk denda
        $this->assertSame(135000, $summary['late_fees']);
    }

    public function test_a_waived_fee_shows_as_zero(): void
    {
        [$company] = $this->lateRental(waivedTo: 0);

        $this->assertSame(0, app(CompanyReport::class)->build($company, now())['summary']['late_fees']);
    }

    public function test_the_late_fee_is_never_taken_from_the_deposit(): void
    {
        [$company] = $this->lateRental();

        $this->assertSame(5000000 - 450000, $company->fresh()->deposit_balance);
    }

    public function test_the_csv_carries_the_late_fee_column_and_its_total(): void
    {
        [, $finance] = $this->lateRental();

        $csv = $this->actingAs($finance)
            ->get(route('corporate.reports.csv', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString(';135000;', $csv);                  // baris pesanan
        $this->assertStringContainsString('TOTAL;450000;135000', $csv);       // baris total
    }
}