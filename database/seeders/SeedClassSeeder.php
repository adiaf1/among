<?php

namespace Database\Seeders;

use App\Models\SeedClass;
use Illuminate\Database\Seeder;

class SeedClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seedClasses = [
            ['code' => 'BS', 'name' => 'Benih Penjenis', 'description' => 'Kelas benih sumber awal yang dihasilkan dan diawasi oleh pemulia.'],
            ['code' => 'FS', 'name' => 'Benih Dasar', 'description' => 'Benih turunan dari benih penjenis untuk perbanyakan berikutnya.'],
            ['code' => 'SS', 'name' => 'Benih Pokok', 'description' => 'Benih turunan dari benih dasar untuk produksi benih sebar.'],
            ['code' => 'ES', 'name' => 'Benih Sebar', 'description' => 'Benih hasil perbanyakan dari benih pokok untuk digunakan petani.'],
            ['code' => 'BR', 'name' => 'Benih Rekomendasi', 'description' => 'Kelas benih operasional untuk kebutuhan internal atau klasifikasi tambahan.'],
        ];

        foreach ($seedClasses as $seedClass) {
            SeedClass::updateOrCreate(
                ['code' => $seedClass['code']],
                [
                    'name' => $seedClass['name'],
                    'description' => $seedClass['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
