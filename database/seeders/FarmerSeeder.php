<?php

namespace Database\Seeders;

use App\Models\Farmer;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $farmers = [
            [
                'code' => 'PTR001',
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'identity_number' => '3201010101010001',
                'address' => 'Desa Sukamaju, Kecamatan Ciasem',
                'notes' => 'Petani mitra aktif untuk produksi benih padi.',
            ],
            [
                'code' => 'PTR002',
                'name' => 'Siti Aminah',
                'phone' => '082233445566',
                'identity_number' => '3201010101010002',
                'address' => 'Desa Mekarsari, Kecamatan Pamanukan',
                'notes' => 'Memiliki pengalaman penangkaran benih.',
            ],
            [
                'code' => 'PTR003',
                'name' => 'Joko Prasetyo',
                'phone' => '083344556677',
                'identity_number' => '3201010101010003',
                'address' => 'Desa Karangmulya, Kecamatan Binong',
                'notes' => 'Lahan potensial untuk musim tanam berikutnya.',
            ],
            [
                'code' => 'PTR004',
                'name' => 'Dewi Lestari',
                'phone' => '085677889900',
                'identity_number' => '3201010101010004',
                'address' => 'Desa Tanjungsari, Kecamatan Pagaden',
                'notes' => 'Petani kooperator dengan catatan produksi baik.',
            ],
            [
                'code' => 'PTR005',
                'name' => 'Agus Setiawan',
                'phone' => '087788990011',
                'identity_number' => '3201010101010005',
                'address' => 'Desa Rawamekar, Kecamatan Subang',
                'notes' => 'Mitra baru untuk pengembangan produksi benih.',
            ],
        ];

        foreach ($farmers as $farmer) {
            Farmer::updateOrCreate(
                ['code' => $farmer['code']],
                [
                    'name' => $farmer['name'],
                    'phone' => $farmer['phone'],
                    'identity_number' => $farmer['identity_number'],
                    'address' => $farmer['address'],
                    'notes' => $farmer['notes'],
                    'is_active' => true,
                ]
            );
        }
    }
}
