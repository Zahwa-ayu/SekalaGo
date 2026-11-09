<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function test_the_application_runs_on_jakarta_time(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('+07:00', now()->format('P'));
    }

    public function test_early_morning_in_wib_is_still_the_same_day(): void
    {
        // 05.00 WIB = 22.00 UTC hari sebelumnya: jika zona waktu salah, tanggalnya bergeser
        $earlyMorning = Carbon::parse('2026-11-10 05:00:00');

        $this->assertSame('2026-11-10', $earlyMorning->toDateString());
        $this->assertSame('2026-11-09', $earlyMorning->copy()->utc()->toDateString());
    }

    public function test_the_hold_deadline_is_stored_and_read_back_in_wib(): void
    {
        $room    = $this->makeRoomType($this->makeHotel($this->makePartner()), quota: 1);
        $booking = $this->bookRoom($this->makeCustomer(), $room);   // dibuat jam 10.00 WIB (waktu dibekukan)

        $this->assertSame('10:30', $booking->fresh()->expires_at->format('H:i'));
        $this->assertSame('2026-11-10 10:30:00', DB::table('bookings')->where('id', $booking->id)->value('expires_at'));
    }
}