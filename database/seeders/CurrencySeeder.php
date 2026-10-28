<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'IDR', 'symbol' => 'Rp', 'name' => 'Rupiah',     'decimal_places' => 0, 'rate_to_base' => 1],
            ['code' => 'USD', 'symbol' => '$',  'name' => 'US Dollar',  'decimal_places' => 2, 'rate_to_base' => 16500],
            ['code' => 'KRW', 'symbol' => '₩',  'name' => 'Korean Won', 'decimal_places' => 0, 'rate_to_base' => 12],
        ];

        foreach ($rows as $row) {
            Currency::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}