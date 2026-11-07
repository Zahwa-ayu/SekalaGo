<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoVehicleSeeder extends Seeder
{
    public function run(): void
    {
        $partner = User::where('email', 'mitra@sekalago.test')->first()?->partner;

        if (! $partner) {
            $this->command?->error('Mitra contoh (mitra@sekalago.test) tidak ditemukan. Jalankan UserSeeder dulu.');

            return;
        }

        if ($partner->vehicles()->exists()) {
            $this->command?->warn('Mitra ini sudah punya armada, seeder demo dilewati.');

            return;
        }

        foreach ($this->fleet() as $row) {
            $vehicle = Vehicle::create([
                'partner_id'         => $partner->id,
                'city'               => $row['city'],
                'area'               => $row['area'],
                'address'            => $row['address'],
                'type'               => $row['type'],
                'transmission'       => $row['transmission'],
                'fuel_type'          => $row['fuel_type'],
                'seats'              => $row['seats'],
                'luggage_capacity'   => $row['luggage'],
                'model_year'         => $row['year'],
                'features'           => $row['features'],
                'units'              => $row['units'],
                'price_per_day'      => $row['price'],
                'min_days'           => 1,
                'driver_mode'        => $row['driver_mode'],
                'driver_fee_per_day' => $row['driver_fee'],
                'extras_note'        => 'BBM, tol, dan parkir ditanggung penyewa.',
                'status'             => $row['status'],
                'source_locale'      => 'id',
            ]);

            $vehicle->translations()->createMany([
                ['locale' => 'id', 'name' => $row['name'], 'description' => $row['desc_id'], 'is_auto_translated' => false],
                ['locale' => 'en', 'name' => $row['name'], 'description' => $row['desc_en'], 'is_auto_translated' => false],
            ]);

            // Armada berstatus menunggu sengaja tanpa foto, untuk mencoba aturan tinjauan admin
            if ($row['status'] === 'approved') {
                foreach (['Tampak depan', 'Interior'] as $n => $caption) {
                    $path = "vehicles/{$vehicle->id}/demo-" . ($n + 1) . '.svg';

                    Storage::disk('public')->put($path, $this->svg($row['name'], $caption, $row['colors'], $n));

                    $vehicle->photos()->create([
                        'path'       => $path,
                        'is_cover'   => $n === 0,
                        'sort_order' => $n,
                    ]);
                }
            }

            if ($row['blackout'] ?? false) {
                $vehicle->blackouts()->create([
                    'start_date' => today()->addDays(20),
                    'end_date'   => today()->addDays(22),
                    'note'       => 'Servis rutin',
                ]);
            }
        }

        $this->command?->info('Armada demo dibuat. Jalankan "php artisan storage:link" jika foto belum tampil.');
    }

    private function fleet(): array
    {
        return [
            [
                'name' => 'Toyota Avanza', 'type' => 'mpv', 'transmission' => 'manual', 'fuel_type' => 'petrol',
                'seats' => 7, 'luggage' => 3, 'year' => 2022, 'features' => ['ac', 'audio', 'usb'],
                'city' => 'Yogyakarta', 'area' => 'Sleman', 'address' => 'Jl. Affandi No. 10',
                'units' => 3, 'price' => 450000, 'driver_mode' => 'both', 'driver_fee' => 250000,
                'status' => 'approved', 'colors' => ['#0077b6', '#031e38'], 'blackout' => true,
                'desc_id' => 'MPV keluarga yang irit dan nyaman untuk wisata dalam kota maupun luar kota.',
                'desc_en' => 'An economical, comfortable family MPV for city and out-of-town trips.',
            ],
            [
                'name' => 'Toyota Innova Zenix Hybrid', 'type' => 'mpv', 'transmission' => 'automatic', 'fuel_type' => 'hybrid',
                'seats' => 7, 'luggage' => 4, 'year' => 2023, 'features' => ['ac', 'audio', 'usb', 'gps', 'airbag'],
                'city' => 'Yogyakarta', 'area' => 'Depok', 'address' => 'Jl. Kaliurang Km 5',
                'units' => 2, 'price' => 800000, 'driver_mode' => 'both', 'driver_fee' => 300000,
                'status' => 'approved', 'colors' => ['#00b4d8', '#0b3c5d'],
                'desc_id' => 'MPV hybrid yang senyap dan hemat bahan bakar, cocok untuk perjalanan jauh bersama keluarga.',
                'desc_en' => 'A quiet, fuel-efficient hybrid MPV, ideal for long trips with family.',
            ],
            [
                'name' => 'Honda Brio', 'type' => 'city_car', 'transmission' => 'manual', 'fuel_type' => 'petrol',
                'seats' => 4, 'luggage' => 2, 'year' => 2021, 'features' => ['ac', 'audio'],
                'city' => 'Denpasar', 'area' => 'Kuta', 'address' => 'Jl. Raya Kuta No. 88',
                'units' => 4, 'price' => 300000, 'driver_mode' => 'self_drive', 'driver_fee' => null,
                'status' => 'approved', 'colors' => ['#f4a261', '#e76f51'],
                'desc_id' => 'Mobil kecil lincah untuk berkeliling Bali, mudah parkir dan irit.',
                'desc_en' => 'A nimble small car for getting around Bali, easy to park and economical.',
            ],
            [
                'name' => 'Toyota Alphard', 'type' => 'luxury', 'transmission' => 'automatic', 'fuel_type' => 'petrol',
                'seats' => 7, 'luggage' => 4, 'year' => 2022, 'features' => ['ac', 'audio', 'usb', 'gps', 'sunroof', 'airbag'],
                'city' => 'Denpasar', 'area' => 'Sanur', 'address' => 'Jl. Danau Tamblingan No. 21',
                'units' => 1, 'price' => 2500000, 'driver_mode' => 'with_driver', 'driver_fee' => 400000,
                'status' => 'approved', 'colors' => ['#031e38', '#0077b6'],
                'desc_id' => 'Kendaraan mewah dengan kabin lega untuk tamu penting, dilengkapi supir berpengalaman.',
                'desc_en' => 'A luxury vehicle with a spacious cabin for VIP guests, with an experienced driver.',
            ],
            [
                'name' => 'Isuzu Elf Long', 'type' => 'minibus', 'transmission' => 'manual', 'fuel_type' => 'diesel',
                'seats' => 15, 'luggage' => 8, 'year' => 2020, 'features' => ['ac', 'audio', 'usb'],
                'city' => 'Cirebon', 'area' => 'Kesambi', 'address' => 'Jl. Tuparev No. 45',
                'units' => 2, 'price' => 1200000, 'driver_mode' => 'with_driver', 'driver_fee' => 350000,
                'status' => 'approved', 'colors' => ['#0b3c5d', '#00b4d8'],
                'desc_id' => 'Minibus untuk rombongan, kantor, atau keluarga besar dengan bagasi luas.',
                'desc_en' => 'A minibus for groups, offices or large families, with generous luggage space.',
            ],
            [
                'name' => 'Toyota Fortuner', 'type' => 'suv', 'transmission' => 'automatic', 'fuel_type' => 'diesel',
                'seats' => 7, 'luggage' => 4, 'year' => 2022, 'features' => ['ac', 'audio', 'usb', 'gps', 'roof_rack', 'airbag'],
                'city' => 'Jakarta', 'area' => 'Kebayoran Baru', 'address' => 'Jl. Senopati No. 12',
                'units' => 2, 'price' => 1100000, 'driver_mode' => 'both', 'driver_fee' => 350000,
                'status' => 'approved', 'colors' => ['#e76f51', '#031e38'],
                'desc_id' => 'SUV tangguh untuk perjalanan dinas dan wisata, nyaman di segala medan.',
                'desc_en' => 'A rugged SUV for business trips and holidays, comfortable on any terrain.',
            ],
            [
                'name' => 'Daihatsu Xenia', 'type' => 'mpv', 'transmission' => 'manual', 'fuel_type' => 'petrol',
                'seats' => 7, 'luggage' => 3, 'year' => 2021, 'features' => ['ac', 'audio'],
                'city' => 'Yogyakarta', 'area' => 'Gondokusuman', 'address' => 'Jl. Kusumanegara No. 5',
                'units' => 2, 'price' => 400000, 'driver_mode' => 'both', 'driver_fee' => 230000,
                'status' => 'pending', 'colors' => ['#0077b6', '#caf0f8'],
                'desc_id' => 'Armada baru yang menunggu tinjauan admin.',
                'desc_en' => 'A new vehicle awaiting admin review.',
            ],
        ];
    }

    /** Gambar pengganti berbentuk SVG: latar gradasi, siluet mobil, dan nama armada. */
    private function svg(string $title, string $caption, array $colors, int $variant): string
    {
        [$from, $to] = $variant === 0 ? $colors : array_reverse($colors);

        $title   = htmlspecialchars($title, ENT_XML1);
        $caption = htmlspecialchars($caption, ENT_XML1);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 750" width="1200" height="750">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="750" fill="url(#g)"/>
  <g fill="#ffffff" fill-opacity="0.92">
    <path d="M250 470 L330 330 Q345 305 380 305 L820 305 Q855 305 875 330 L960 470 Z"/>
    <rect x="200" y="455" width="800" height="110" rx="40"/>
    <circle cx="400" cy="575" r="62"/>
    <circle cx="800" cy="575" r="62"/>
  </g>
  <g fill="#0b3c5d">
    <circle cx="400" cy="575" r="30"/>
    <circle cx="800" cy="575" r="30"/>
    <path d="M355 335 L395 335 L395 450 L300 450 Z" fill-opacity="0.35"/>
    <path d="M445 335 L640 335 L640 450 L445 450 Z" fill-opacity="0.35"/>
    <path d="M690 335 L790 335 L850 450 L690 450 Z" fill-opacity="0.35"/>
  </g>
  <text x="60" y="690" font-family="Arial, sans-serif" font-size="54" font-weight="700" fill="#ffffff">{$title}</text>
  <text x="60" y="728" font-family="Arial, sans-serif" font-size="26" fill="#ffffff" fill-opacity="0.85">{$caption}</text>
</svg>
SVG;
    }
}