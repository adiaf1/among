<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'KG', 'name' => 'Kilogram', 'type' => 'weight'],
            ['code' => 'TON', 'name' => 'Ton', 'type' => 'weight'],
            ['code' => 'ZAK50', 'name' => 'Zak 50kg', 'type' => 'packaging'],
            ['code' => 'ZAK25', 'name' => 'Zak 25kg', 'type' => 'packaging'],
            ['code' => 'ZAK10', 'name' => 'Zak 10kg', 'type' => 'packaging'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
