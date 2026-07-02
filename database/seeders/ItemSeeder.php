<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ciherang = RiceVariety::where('code', 'VRT001')->first();
        $inpari32 = RiceVariety::where('code', 'VRT002')->first();
        $mekongga = RiceVariety::where('code', 'VRT003')->first();
        $es = SeedClass::where('code', 'KB004')->first();
        $ss = SeedClass::where('code', 'KB003')->first();

        $items = [
            [
                'code' => 'BRG001',
                'name' => 'Benih Padi Ciherang ES',
                'category' => 'benih',
                'material_state' => 'benih_jadi',
                'unit' => 'kg',
                'rice_variety_id' => $ciherang?->id,
                'seed_class_id' => $es?->id,
                'minimum_stock' => 500,
                'description' => 'Benih siap jual varietas Ciherang kelas ES.',
            ],
            [
                'code' => 'BRG002',
                'name' => 'Benih Padi Inpari 32 ES',
                'category' => 'benih',
                'material_state' => 'benih_jadi',
                'unit' => 'kg',
                'rice_variety_id' => $inpari32?->id,
                'seed_class_id' => $es?->id,
                'minimum_stock' => 500,
                'description' => 'Benih siap jual varietas Inpari 32 kelas ES.',
            ],
            [
                'code' => 'BRG003',
                'name' => 'Gabah Calon Benih Mekongga SS Basah',
                'category' => 'gabah',
                'material_state' => 'basah',
                'unit' => 'kg',
                'rice_variety_id' => $mekongga?->id,
                'seed_class_id' => $ss?->id,
                'minimum_stock' => 1000,
                'description' => 'Gabah calon benih untuk proses produksi.',
            ],
            [
                'code' => 'BRG007',
                'name' => 'Gabah Calon Benih Mekongga SS Kering',
                'category' => 'gabah',
                'material_state' => 'kering',
                'unit' => 'kg',
                'rice_variety_id' => $mekongga?->id,
                'seed_class_id' => $ss?->id,
                'minimum_stock' => 1000,
                'description' => 'Gabah calon benih hasil pengeringan untuk proses lanjutan.',
            ],
            [
                'code' => 'BRG004',
                'name' => 'Karung Kemasan 5 Kg',
                'category' => 'karung',
                'material_state' => 'bahan_pendukung',
                'unit' => 'pcs',
                'minimum_stock' => 200,
                'description' => 'Karung kemasan benih ukuran 5 kg.',
            ],
            [
                'code' => 'BRG005',
                'name' => 'Plastik Inner Kemasan',
                'category' => 'plastik',
                'material_state' => 'bahan_pendukung',
                'unit' => 'pcs',
                'minimum_stock' => 500,
                'description' => 'Plastik inner untuk kemasan benih.',
            ],
            [
                'code' => 'BRG006',
                'name' => 'Benang Karung',
                'category' => 'benang_karung',
                'material_state' => 'bahan_pendukung',
                'unit' => 'roll',
                'minimum_stock' => 10,
                'description' => 'Benang untuk menjahit karung kemasan.',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['code' => $item['code']],
                $item + ['is_active' => true]
            );
        }
    }
}
