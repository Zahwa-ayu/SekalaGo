<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->cities() as $i => [$name, $province, $aliases, $featured]) {
            $city = City::firstOrNew(['name' => $name]);

            // Kota baru: ikut pengaturan awal. Kota yang sudah ada (misalnya dari data lama): hanya lengkapi yang kosong.
            if (! $city->exists) {
                $city->is_featured = $featured;
                $city->is_active   = true;
                $city->sort_order  = $featured ? $i + 1 : 100;
            }

            $city->province ??= $province;
            $city->aliases  ??= $aliases;
            $city->save();
        }
    }

    /** @return list<array{0: string, 1: string, 2: ?string, 3: bool}> nama, provinsi, alias, tampil di Kota Favorit */
    private function cities(): array
    {
        return [
            ['Yogyakarta', 'DI Yogyakarta', 'Jogja, Yogya, Jogjakarta, DIY', true],
            ['Denpasar', 'Bali', 'Bali, Kuta, Seminyak, Sanur, Ubud, Nusa Dua', true],
            ['Jakarta', 'DKI Jakarta', 'DKI, Jakarta Pusat, Jakarta Selatan, Jakarta Barat', true],
            ['Bandung', 'Jawa Barat', 'Lembang', true],
            ['Labuan Bajo', 'Nusa Tenggara Timur', 'Komodo, Flores', true],
            ['Surabaya', 'Jawa Timur', 'Sby', true],
            ['Malang', 'Jawa Timur', 'Batu', true],
            ['Makassar', 'Sulawesi Selatan', 'Ujung Pandang', true],
            ['Semarang', 'Jawa Tengah', null, false],
            ['Surakarta', 'Jawa Tengah', 'Solo', false],
            ['Magelang', 'Jawa Tengah', 'Borobudur', false],
            ['Jepara', 'Jawa Tengah', 'Karimunjawa', false],
            ['Cirebon', 'Jawa Barat', null, false],
            ['Bogor', 'Jawa Barat', 'Puncak', false],
            ['Bekasi', 'Jawa Barat', null, false],
            ['Tangerang', 'Banten', null, false],
            ['Banyuwangi', 'Jawa Timur', 'Ijen', false],
            ['Kupang', 'Nusa Tenggara Timur', null, false],
            ['Mataram', 'Nusa Tenggara Barat', 'Lombok, Senggigi, Gili', false],
            ['Manado', 'Sulawesi Utara', 'Bunaken', false],
            ['Medan', 'Sumatera Utara', 'Danau Toba', false],
            ['Banda Aceh', 'Aceh', null, false],
            ['Padang', 'Sumatera Barat', 'Bukittinggi', false],
            ['Pekanbaru', 'Riau', null, false],
            ['Batam', 'Kepulauan Riau', 'Kepri', false],
            ['Palembang', 'Sumatera Selatan', null, false],
            ['Bandar Lampung', 'Lampung', 'Lampung', false],
            ['Pontianak', 'Kalimantan Barat', null, false],
            ['Balikpapan', 'Kalimantan Timur', null, false],
            ['Samarinda', 'Kalimantan Timur', null, false],
            ['Berau', 'Kalimantan Timur', 'Derawan', false],
            ['Banjarmasin', 'Kalimantan Selatan', null, false],
            ['Ambon', 'Maluku', 'Banda Neira', false],
            ['Jayapura', 'Papua', null, false],
            ['Sorong', 'Papua Barat Daya', 'Raja Ampat', false],
        ];
    }
}