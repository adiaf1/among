<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'GDG001',
                'name' => 'Gudang Benih Utama',
                'person_in_charge' => 'Arif Setiawan',
                'phone' => '083100000001',
                'address' => 'Jl. Produksi Benih No. 1',
                'capacity_kg' => 25000,
                'notes' => 'Gudang utama untuk benih siap jual.',
            ],
            [
                'code' => 'GDG002',
                'name' => 'Gudang Bahan Baku',
                'person_in_charge' => 'Lilis Suryani',
                'phone' => '083100000002',
                'address' => 'Area Pengeringan Blok A',
                'capacity_kg' => 40000,
                'notes' => 'Penyimpanan gabah calon benih.',
            ],
            [
                'code' => 'GDG003',
                'name' => 'Gudang Kemasan',
                'person_in_charge' => 'Dedi Kurniawan',
                'phone' => '083100000003',
                'address' => 'Jl. Logistik No. 7',
                'capacity_kg' => 8000,
                'notes' => 'Gudang karung, label, dan kemasan.',
            ],
            [
                'code' => 'GDG004',
                'name' => 'Gudang Transit',
                'person_in_charge' => 'Murni Lestari',
                'phone' => '083100000004',
                'address' => 'Pintu Distribusi Selatan',
                'capacity_kg' => 12000,
                'notes' => 'Transit barang masuk dan keluar.',
            ],
            [
                'code' => 'GDG005',
                'name' => 'Gudang Retur',
                'person_in_charge' => 'Rangga Prakoso',
                'phone' => '083100000005',
                'address' => 'Area Sortasi Belakang',
                'capacity_kg' => 5000,
                'notes' => 'Penyimpanan barang retur atau perlu pemeriksaan.',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(
                ['code' => $warehouse['code']],
                $warehouse + ['is_active' => true]
            );
        }
    }
}
