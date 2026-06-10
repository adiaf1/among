<?php

namespace Database\Seeders;

use App\Models\RiceVariety;
use Illuminate\Database\Seeder;

class RiceVarietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $varieties = [
            ['code' => 'INP32', 'name' => 'Inpari 32', 'description' => 'Varietas padi sawah dengan produktivitas baik dan umum digunakan petani.'],
            ['code' => 'INP42', 'name' => 'Inpari 42', 'description' => 'Varietas padi sawah populer untuk produksi beras premium.'],
            ['code' => 'INP43', 'name' => 'Inpari 43', 'description' => 'Varietas padi sawah dengan potensi hasil tinggi.'],
            ['code' => 'CIH', 'name' => 'Ciherang', 'description' => 'Varietas padi yang luas dibudidayakan dan dikenal stabil.'],
            ['code' => 'MKG', 'name' => 'Mekongga', 'description' => 'Varietas padi sawah adaptif untuk banyak wilayah tanam.'],
            ['code' => 'IR64', 'name' => 'IR 64', 'description' => 'Varietas padi lama yang masih dikenal luas oleh petani.'],
            ['code' => 'CIG', 'name' => 'Cigeulis', 'description' => 'Varietas padi sawah dengan karakter adaptasi yang baik.'],
            ['code' => 'SITB', 'name' => 'Situ Bagendit', 'description' => 'Varietas padi yang dapat digunakan pada lahan sawah dan gogo tertentu.'],
            ['code' => 'MEMB', 'name' => 'Memberamo', 'description' => 'Varietas padi dengan potensi hasil baik dan dikenal di beberapa sentra produksi.'],
            ['code' => 'LOGA', 'name' => 'Logawa', 'description' => 'Varietas padi yang umum digunakan sebagai pilihan produksi.'],
        ];

        foreach ($varieties as $variety) {
            RiceVariety::updateOrCreate(
                ['code' => $variety['code']],
                [
                    'name' => $variety['name'],
                    'description' => $variety['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
