<?php

namespace App\Console\Commands;

use App\Services\Reminders;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:remind';

    protected $description = 'Kirim pengingat pesanan (pembayaran, kedatangan, pengembalian mobil, ulasan)';

    public function handle(Reminders $reminders): int
    {
        foreach ($reminders->run() as $kind => $count) {
            $this->line("{$kind}: {$count}");
        }

        return self::SUCCESS;
    }
}