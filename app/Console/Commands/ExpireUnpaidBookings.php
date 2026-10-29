<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireUnpaidBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Batalkan pesanan yang belum dibayar sampai batas waktu dan lepas kamarnya';

    public function handle(BookingService $service): int
    {
        $count = $service->expireOverdue();
        $this->info("{$count} pesanan kedaluwarsa.");

        return self::SUCCESS;
    }
}