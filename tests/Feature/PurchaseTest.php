<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_purchases(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = $this->supplier();

        Purchase::create([
            'number' => 'PO-001',
            'purchase_date' => '2026-06-10',
            'supplier_id' => $supplier->id,
            'total_amount' => 1500000,
        ]);

        $response = $this->actingAs($admin)->get(route('purchases.index'));

        $response->assertOk();
        $response->assertSee('PO-001');
        $response->assertSee('CV Agro Makmur');
    }

    public function test_admin_can_render_create_purchase_page(): void
    {
        $admin = $this->userWithRole('admin');
        $this->supplier();
        $this->itemAndWarehouse();

        $response = $this->actingAs($admin)->get(route('purchases.create'));

        $response->assertOk();
        $response->assertSee('Tambah Pembelian Barang');
        $response->assertSee('Tambah Baris');
    }

    public function test_editor_can_create_purchase_and_increase_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $supplier = $this->supplier();
        [$item, $warehouse] = $this->itemAndWarehouse();

        $response = $this->actingAs($editor)->post(route('purchases.store'), [
            'number' => 'PO-002',
            'purchase_date' => '2026-06-10',
            'supplier_id' => $supplier->id,
            'notes' => 'Pembelian awal.',
            'items' => [
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 1000,
                    'unit_price' => 1500,
                ],
            ],
        ]);

        $purchase = Purchase::first();

        $response->assertRedirect(route('purchases.show', $purchase));
        $this->assertDatabaseHas('purchases', [
            'number' => 'PO-002',
            'supplier_id' => $supplier->id,
            'total_amount' => 1500000,
        ]);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
            'unit_price' => 1500,
            'subtotal' => 1500000,
        ]);
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'purchase',
            'quantity_in' => 1000,
            'quantity_out' => 0,
            'balance_after' => 1000,
            'reference_number' => 'PO-002',
        ]);
    }

    public function test_purchase_adds_to_existing_stock(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = $this->supplier();
        [$item, $warehouse] = $this->itemAndWarehouse();

        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 500,
        ]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'number' => 'PO-003',
            'purchase_date' => '2026-06-10',
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 250,
                    'unit_price' => 2000,
                ],
            ],
        ]);

        $purchase = Purchase::first();

        $response->assertRedirect(route('purchases.show', $purchase));
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 750,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'purchase',
            'quantity_in' => 250,
            'balance_after' => 750,
        ]);
    }

    public function test_admin_can_view_purchase_detail(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = $this->supplier();
        $purchase = Purchase::create([
            'number' => 'PO-004',
            'purchase_date' => '2026-06-10',
            'supplier_id' => $supplier->id,
            'total_amount' => 0,
        ]);

        $response = $this->actingAs($admin)->get(route('purchases.show', $purchase));

        $response->assertOk();
        $response->assertSee('Detail Pembelian');
        $response->assertSee('PO-004');
    }

    public function test_guest_role_cannot_access_purchases(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('purchases.index'));

        $response->assertForbidden();
    }

    private function supplier(): Supplier
    {
        return Supplier::create([
            'code' => 'SUP001',
            'name' => 'CV Agro Makmur',
            'is_active' => true,
        ]);
    }

    private function itemAndWarehouse(): array
    {
        $item = Item::create([
            'code' => 'BRG001',
            'name' => 'Karung Kemasan 5 Kg',
            'category' => 'kemasan',
            'unit' => 'pcs',
            'minimum_stock' => 200,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Kemasan',
            'is_active' => true,
        ]);

        return [$item, $warehouse];
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
