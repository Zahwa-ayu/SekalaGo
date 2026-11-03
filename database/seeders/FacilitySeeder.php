<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // Hotel dan kamar
            'wifi' => 'both',

            // Fasilitas hotel
            'pool' => 'hotel', 'parking' => 'hotel', 'restaurant' => 'hotel', 'gym' => 'hotel',
            'spa' => 'hotel', 'elevator' => 'hotel', 'reception_24h' => 'hotel',
            'airport_shuttle' => 'hotel', 'meeting_room' => 'hotel', 'laundry' => 'hotel',
            'accessible' => 'hotel', 'garden' => 'hotel', 'breakfast' => 'hotel',

            // Fasilitas kamar
            'ac' => 'room', 'tv' => 'room', 'hot_water' => 'room', 'minibar' => 'room',
            'kettle' => 'room', 'desk' => 'room', 'wardrobe' => 'room', 'balcony' => 'room',
            'sea_view' => 'room', 'bathtub' => 'room', 'hairdryer' => 'room', 'safe' => 'room',
            'refrigerator' => 'room',
        ];

        foreach ($facilities as $code => $scope) {
            Facility::updateOrCreate(['code' => $code], ['scope' => $scope]);
        }
    }
}