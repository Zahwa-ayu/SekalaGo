<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['wifi', 'pool', 'ac', 'parking', 'breakfast'] as $code) {
            Facility::firstOrCreate(['code' => $code]);
        }
    }
}