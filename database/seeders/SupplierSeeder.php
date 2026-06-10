<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP001',
                'name' => 'CV Agro Makmur',
                'contact_person' => 'Rina Wulandari',
                'phone' => '081234560001',
                'email' => 'agromakmur@example.com',
                'address' => 'Jl. Raya Produksi No. 12',
                'notes' => 'Supplier karung dan kemasan.',
            ],
            [
                'code' => 'SUP002',
                'name' => 'Tani Jaya Sejahtera',
                'contact_person' => 'Hendra Saputra',
                'phone' => '081234560002',
                'email' => 'tanijaya@example.com',
                'address' => 'Jl. Benih Unggul No. 8',
                'notes' => 'Supplier pupuk dan pestisida.',
            ],
            [
                'code' => 'SUP003',
                'name' => 'UD Sumber Tani',
                'contact_person' => 'Maya Kartika',
                'phone' => '081234560003',
                'email' => 'sumbertani@example.com',
                'address' => 'Pasar Agro Blok B-17',
                'notes' => 'Supplier alat produksi.',
            ],
            [
                'code' => 'SUP004',
                'name' => 'PT Prima Benih Nusantara',
                'contact_person' => 'Dimas Pratama',
                'phone' => '081234560004',
                'email' => 'primabenih@example.com',
                'address' => 'Kawasan Industri Pangan Kav. 4',
                'notes' => 'Supplier bahan pendukung benih.',
            ],
            [
                'code' => 'SUP005',
                'name' => 'Koperasi Mitra Sawah',
                'contact_person' => 'Nur Azizah',
                'phone' => '081234560005',
                'email' => 'mitrasawah@example.com',
                'address' => 'Desa Sukamaju RT 03 RW 02',
                'notes' => 'Mitra lokal bahan operasional.',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['code' => $supplier['code']],
                $supplier + ['is_active' => true]
            );
        }
    }
}
