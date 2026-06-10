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
                'unit' => 'kg',
                'rice_variety_id' => $inpari32?->id,
                'seed_class_id' => $es?->id,
                'minimum_stock' => 500,
                'description' => 'Benih siap jual varietas Inpari 32 kelas ES.',
            ],
            [
                'code' => 'BRG003',
                'name' => 'Gabah Calon Benih Mekongga SS',
                'category' => 'gabah',
                'unit' => 'kg',
                'rice_variety_id' => $mekongga?->id,
                'seed_class_id' => $ss?->id,
                'minimum_stock' => 1000,
                'description' => 'Gabah calon benih untuk proses produksi.',
            ],
            [
                'code' => 'BRG004',
                'name' => 'Karung Kemasan 5 Kg',
                'category' => 'kemasan',
                'unit' => 'pcs',
                'minimum_stock' => 200,
                'description' => 'Karung kemasan benih ukuran 5 kg.',
            ],
            [
                'code' => 'BRG005',
                'name' => 'Label Sertifikasi Benih',
                'category' => 'kemasan',
                'unit' => 'pcs',
                'minimum_stock' => 500,
                'description' => 'Label sertifikasi untuk kemasan benih.',
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
