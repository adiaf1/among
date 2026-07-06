<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\Land;
use Illuminate\Database\Seeder;

class LandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lands = [
            [
                'farmer_code' => 'PTR001',
                'code' => 'LHN001',
                'name' => 'Sawah Blok Timur',
                'area_size' => 2.50,
                'location' => 'Desa Sukamaju, Kecamatan Ciasem',
                'latitude' => -6.3432100,
                'longitude' => 107.7045100,
                'soil_type' => 'Aluvial',
                'irrigation_type' => 'Teknis',
                'ownership_status' => 'Milik sendiri',
                'certification_status' => 'layak',
                'notes' => 'Lahan produktif untuk produksi benih musim utama.',
            ],
            [
                'farmer_code' => 'PTR002',
                'code' => 'LHN002',
                'name' => 'Sawah Blok Barat',
                'area_size' => 1.75,
                'location' => 'Desa Mekarsari, Kecamatan Pamanukan',
                'latitude' => -6.2847000,
                'longitude' => 107.8176200,
                'soil_type' => 'Lempung',
                'irrigation_type' => 'Setengah teknis',
                'ownership_status' => 'Sewa',
                'certification_status' => 'perlu_perbaikan',
                'notes' => 'Akses jalan mudah untuk angkut hasil panen.',
            ],
            [
                'farmer_code' => 'PTR003',
                'code' => 'LHN003',
                'name' => 'Lahan Karangmulya',
                'area_size' => 3.00,
                'location' => 'Desa Karangmulya, Kecamatan Binong',
                'latitude' => -6.4571200,
                'longitude' => 107.7908200,
                'soil_type' => 'Liat berpasir',
                'irrigation_type' => 'Teknis',
                'ownership_status' => 'Milik keluarga',
                'certification_status' => 'layak',
                'notes' => 'Cocok untuk perluasan produksi benih.',
            ],
            [
                'farmer_code' => 'PTR004',
                'code' => 'LHN004',
                'name' => 'Sawah Tanjungsari',
                'area_size' => 2.20,
                'location' => 'Desa Tanjungsari, Kecamatan Pagaden',
                'latitude' => -6.4789300,
                'longitude' => 107.8034100,
                'soil_type' => 'Aluvial',
                'irrigation_type' => 'Tadah hujan',
                'ownership_status' => 'Milik sendiri',
                'certification_status' => 'belum_ditinjau',
                'notes' => 'Perlu penyesuaian jadwal tanam mengikuti curah hujan.',
            ],
            [
                'farmer_code' => 'PTR005',
                'code' => 'LHN005',
                'name' => 'Lahan Rawamekar',
                'area_size' => 1.50,
                'location' => 'Desa Rawamekar, Kecamatan Subang',
                'latitude' => -6.5620000,
                'longitude' => 107.7600000,
                'soil_type' => 'Lempung berdebu',
                'irrigation_type' => 'Pompa',
                'ownership_status' => 'Sewa',
                'certification_status' => 'belum_ditinjau',
                'notes' => 'Mitra baru, perlu pemantauan awal lebih rutin.',
            ],
        ];

        foreach ($lands as $land) {
            $farmer = Farmer::where('code', $land['farmer_code'])->first();

            if (! $farmer) {
                continue;
            }

            Land::updateOrCreate(
                ['code' => $land['code']],
                [
                    'farmer_id' => $farmer->id,
                    'name' => $land['name'],
                    'area_size' => $land['area_size'],
                    'location' => $land['location'],
                    'latitude' => $land['latitude'],
                    'longitude' => $land['longitude'],
                    'soil_type' => $land['soil_type'],
                    'irrigation_type' => $land['irrigation_type'],
                    'ownership_status' => $land['ownership_status'],
                    'certification_status' => $land['certification_status'],
                    'notes' => $land['notes'],
                    'is_active' => true,
                ]
            );
        }
    }
}
