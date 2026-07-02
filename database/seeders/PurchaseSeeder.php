<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RiceVarietySeeder::class,
            SeedClassSeeder::class,
            FarmerSeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            ItemSeeder::class,
        ]);

        $purchases = [
            [
                'number' => 'PO-DMY-001',
                'purchase_date' => '2026-06-01',
                'supplier_code' => 'SUP001',
                'transport_type' => 'Pickup',
                'vehicle_plate_number' => 'T 1021 AB',
                'notes' => 'Pembelian kemasan awal bulan.',
                'items' => [
                    ['item_code' => 'BRG004', 'warehouse_code' => 'GDG003', 'quantity' => 1000, 'unit_price' => 1500],
                    ['item_code' => 'BRG005', 'warehouse_code' => 'GDG003', 'quantity' => 2000, 'unit_price' => 350],
                ],
            ],
            [
                'number' => 'PO-DMY-002',
                'purchase_date' => '2026-06-03',
                'farmer_code' => 'PTR001',
                'transport_type' => 'Truk Engkel',
                'vehicle_plate_number' => 'T 2456 CD',
                'notes' => 'Pembelian gabah calon benih.',
                'items' => [
                    ['item_code' => 'BRG003', 'warehouse_code' => 'GDG002', 'quantity' => 3500, 'unit_price' => 6200],
                ],
            ],
            [
                'number' => 'PO-DMY-003',
                'purchase_date' => '2026-06-05',
                'supplier_code' => 'SUP004',
                'transport_type' => 'Truk Colt Diesel',
                'vehicle_plate_number' => 'D 8891 XY',
                'notes' => 'Pembelian benih siap jual.',
                'items' => [
                    ['item_code' => 'BRG001', 'warehouse_code' => 'GDG001', 'quantity' => 1200, 'unit_price' => 12500],
                    ['item_code' => 'BRG002', 'warehouse_code' => 'GDG001', 'quantity' => 900, 'unit_price' => 13000],
                ],
            ],
            [
                'number' => 'PO-DMY-004',
                'purchase_date' => '2026-06-07',
                'supplier_code' => 'SUP003',
                'transport_type' => 'Pickup',
                'vehicle_plate_number' => 'T 1021 AB',
                'notes' => 'Tambahan kebutuhan kemasan.',
                'items' => [
                    ['item_code' => 'BRG004', 'warehouse_code' => 'GDG003', 'quantity' => 750, 'unit_price' => 1550],
                ],
            ],
            [
                'number' => 'PO-DMY-005',
                'purchase_date' => '2026-06-09',
                'farmer_code' => 'PTR004',
                'transport_type' => 'Truk Engkel',
                'vehicle_plate_number' => 'T 7788 EF',
                'notes' => 'Pembelian bahan dari petani mitra lokal.',
                'items' => [
                    ['item_code' => 'BRG003', 'warehouse_code' => 'GDG002', 'quantity' => 1800, 'unit_price' => 6100],
                    ['item_code' => 'BRG005', 'warehouse_code' => 'GDG003', 'quantity' => 1000, 'unit_price' => 375],
                ],
            ],
        ];

        foreach ($purchases as $purchaseData) {
            if (Purchase::where('number', $purchaseData['number'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($purchaseData) {
                $supplier = isset($purchaseData['supplier_code'])
                    ? Supplier::where('code', $purchaseData['supplier_code'])->firstOrFail()
                    : null;
                $farmer = isset($purchaseData['farmer_code'])
                    ? Farmer::where('code', $purchaseData['farmer_code'])->firstOrFail()
                    : null;
                $totalAmount = collect($purchaseData['items'])->sum(
                    fn (array $item) => (float) $item['quantity'] * (float) $item['unit_price']
                );

                $purchase = Purchase::create([
                    'number' => $purchaseData['number'],
                    'purchase_date' => $purchaseData['purchase_date'],
                    'supplier_id' => $supplier?->id,
                    'farmer_id' => $farmer?->id,
                    'transport_type' => $purchaseData['transport_type'],
                    'vehicle_plate_number' => $purchaseData['vehicle_plate_number'],
                    'total_amount' => $totalAmount,
                    'notes' => $purchaseData['notes'],
                ]);

                foreach ($purchaseData['items'] as $detail) {
                    $item = Item::where('code', $detail['item_code'])->firstOrFail();
                    $warehouse = Warehouse::where('code', $detail['warehouse_code'])->firstOrFail();
                    $quantity = (float) $detail['quantity'];
                    $unitPrice = (float) $detail['unit_price'];
                    $subtotal = $quantity * $unitPrice;

                    $purchaseItem = $purchase->items()->create([
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $stock = Stock::lockForUpdate()->firstOrCreate(
                        [
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse->id,
                        ],
                        ['quantity' => 0]
                    );

                    $balanceAfter = (float) $stock->quantity + $quantity;
                    $stock->update(['quantity' => $balanceAfter]);

                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouse->id,
                        'movement_date' => $purchaseData['purchase_date'],
                        'type' => 'purchase',
                        'quantity_in' => $quantity,
                        'quantity_out' => 0,
                        'balance_after' => $balanceAfter,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchaseItem->id,
                        'reference_number' => $purchase->number,
                        'notes' => $purchase->notes,
                    ]);
                }
            });
        }
    }
}
