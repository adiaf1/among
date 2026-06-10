<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CST001',
                'name' => 'Kios Tani Subur',
                'customer_type' => 'kios',
                'contact_person' => 'Agus Riyanto',
                'phone' => '082100000001',
                'email' => 'tanisubur@example.com',
                'address' => 'Jl. Pasar Tani No. 3',
                'notes' => 'Pelanggan kios benih lokal.',
            ],
            [
                'code' => 'CST002',
                'name' => 'Budi Hartono',
                'customer_type' => 'perorangan',
                'contact_person' => null,
                'phone' => '082100000002',
                'email' => 'budi.hartono@example.com',
                'address' => 'Desa Mekarsari RT 01 RW 04',
                'notes' => 'Petani pembeli benih langsung.',
            ],
            [
                'code' => 'CST003',
                'name' => 'CV Berkah Agro',
                'customer_type' => 'distributor',
                'contact_person' => 'Santi Permata',
                'phone' => '082100000003',
                'email' => 'berkahagro@example.com',
                'address' => 'Komplek Pergudangan Agro Blok C-2',
                'notes' => 'Distributor area kabupaten.',
            ],
            [
                'code' => 'CST004',
                'name' => 'Dinas Pertanian Kecamatan',
                'customer_type' => 'instansi',
                'contact_person' => 'Rahmat Hidayat',
                'phone' => '082100000004',
                'email' => 'distan.kecamatan@example.com',
                'address' => 'Jl. Pemerintahan No. 10',
                'notes' => 'Pelanggan program bantuan benih.',
            ],
            [
                'code' => 'CST005',
                'name' => 'Kios Sumber Makmur',
                'customer_type' => 'kios',
                'contact_person' => 'Nina Lestari',
                'phone' => '082100000005',
                'email' => 'sumbermakmur@example.com',
                'address' => 'Pasar Induk Pertanian Blok A-8',
                'notes' => 'Pelanggan aktif pembelian rutin.',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['code' => $customer['code']],
                $customer + ['is_active' => true]
            );
        }
    }
}
