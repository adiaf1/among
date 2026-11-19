<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_sales(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = $this->customer();

        Sale::create([
            'number' => 'SO-20260610-ABCD',
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_status' => 'lunas',
            'total_amount' => 1500000,
        ]);

        $response = $this->actingAs($admin)->get(route('sales.index'));

        $response->assertOk();
        $response->assertSee('SO-20260610-ABCD');
        $response->assertSee('Kios Tani Makmur');
    }

    public function test_admin_can_render_create_sale_page(): void
    {
        $admin = $this->userWithRole('admin');
        $this->customer();
        $item = $this->item();
        $warehouse = $this->warehouse();

        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => 'LOT-PB-01',
            'quantity' => 500,
        ]);

        $response = $this->actingAs($admin)->get(route('sales.create'));

        $response->assertOk();
        $response->assertSee('Tambah Penjualan Barang');
        $response->assertSee('LOT-PB-01');
    }

    public function test_editor_can_create_sale_and_deduct_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $customer = $this->customer();
        $item = $this->item();
        $warehouse = $this->warehouse();

        $stock = Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => 'LOT-PB-01',
            'quantity' => 1000,
        ]);

        $response = $this->actingAs($editor)->post(route('sales.store'), [
            'number' => 'SO-001',
            'sale_date' => '2026-06-15',
            'customer_id' => $customer->id,
            'payment_status' => 'belum_lunas',
            'transport_type' => 'Pick Up',
            'vehicle_plate_number' => 'BE 1234 XY',
            'notes' => 'Pengiriman benih bersertifikat.',
            'items' => [
                [
                    'stock_id' => $stock->id,
                    'quantity' => 250,
                    'unit_price' => 14000,
                ],
            ],
        ]);

        $sale = Sale::where('number', 'SO-001')->firstOrFail();

        $response->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'number' => 'SO-001',
            'customer_id' => $customer->id,
            'payment_status' => 'belum_lunas',
            'total_amount' => 3500000,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'stock_id' => $stock->id,
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => 'LOT-PB-01',
            'quantity' => 250,
            'unit_price' => 14000,
            'subtotal' => 3500000,
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 750,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => 'sale',
            'quantity_in' => 0,
            'quantity_out' => 250,
            'balance_after' => 750,
            'reference_number' => 'SO-001',
        ]);
    }

    public function test_sale_rejects_insufficient_stock(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = $this->customer();
        $item = $this->item();
        $warehouse = $this->warehouse();

        $stock = Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => 'LOT-PB-02',
            'quantity' => 100,
        ]);

        $response = $this->actingAs($admin)->post(route('sales.store'), [
            'number' => 'SO-FAIL-01',
            'sale_date' => '2026-06-15',
            'customer_id' => $customer->id,
            'payment_status' => 'lunas',
            'items' => [
                [
                    'stock_id' => $stock->id,
                    'quantity' => 150,
                    'unit_price' => 14000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseMissing('sales', [
            'number' => 'SO-FAIL-01',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 100,
        ]);
    }

    public function test_editor_can_update_sale_payment_status(): void
    {
        $editor = $this->userWithRole('editor');
        $customer = $this->customer();

        $sale = Sale::create([
            'number' => 'SO-002',
            'sale_date' => '2026-06-15',
            'customer_id' => $customer->id,
            'payment_status' => 'belum_lunas',
            'total_amount' => 1000000,
        ]);

        $response = $this->actingAs($editor)->patch(route('sales.payment-status.update', $sale), [
            'payment_status' => 'lunas',
        ]);

        $response->assertRedirect(route('sales.show', $sale));
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'payment_status' => 'lunas',
        ]);
    }

    public function test_guest_role_cannot_access_sales(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('sales.index'));

        $response->assertForbidden();
    }

    private function customer(): Customer
    {
        return Customer::create([
            'code' => 'PLG001',
            'name' => 'Kios Tani Makmur',
            'customer_type' => 'kios',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
    }

    private function item(): Item
    {
        return Item::create([
            'code' => 'BNH001',
            'name' => 'Benih Padi Inpari 32 BR',
            'category' => 'benih',
            'material_state' => 'benih_jadi',
            'unit' => 'kg',
            'minimum_stock' => 200,
            'is_active' => true,
        ]);
    }

    private function warehouse(): Warehouse
    {
        return Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Benih Jadi',
            'is_active' => true,
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
