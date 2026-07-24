<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_stocks(): void
    {
        $admin = $this->userWithRole('admin');
        [$item, $warehouse] = $this->itemAndWarehouse();

        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);

        $response = $this->actingAs($admin)->get(route('stocks.index'));

        $response->assertOk();
        $response->assertSee('Benih Padi Ciherang ES');
        $response->assertSee('Gudang Benih Utama');
    }

    public function test_editor_can_create_initial_stock_adjustment(): void
    {
        $editor = $this->userWithRole('editor');
        [$item, $warehouse] = $this->itemAndWarehouse();

        $response = $this->actingAs($editor)->post(route('stocks.store'), [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
            'movement_date' => '2026-06-10',
            'notes' => 'Stok awal.',
        ]);

        $stock = Stock::first();

        $response->assertRedirect(route('stocks.show', $stock));
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'type' => 'adjustment',
            'quantity_in' => 1000,
            'quantity_out' => 0,
            'balance_after' => 1000,
        ]);
    }

    public function test_stock_adjustment_can_use_lot_number(): void
    {
        $editor = $this->userWithRole('editor');
        [$item, $warehouse] = $this->itemAndWarehouse();

        $response = $this->actingAs($editor)->post(route('stocks.store'), [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => ' lot-001 ',
            'quantity' => 500,
            'movement_date' => '2026-06-10',
            'notes' => 'Stok awal lot.',
        ]);

        $stock = Stock::first();

        $response->assertRedirect(route('stocks.show', $stock));
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'lot_number' => 'LOT-001',
            'quantity' => 500,
        ]);
    }

    public function test_adjustment_down_records_quantity_out(): void
    {
        $admin = $this->userWithRole('admin');
        [$item, $warehouse] = $this->itemAndWarehouse();
        $stock = Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);

        $response = $this->actingAs($admin)->post(route('stocks.store'), [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 750,
            'movement_date' => '2026-06-10',
            'notes' => 'Hasil opname.',
        ]);

        $response->assertRedirect(route('stocks.show', $stock));
        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'quantity' => 750,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $stock->id,
            'quantity_in' => 0,
            'quantity_out' => 250,
            'balance_after' => 750,
        ]);
    }

    public function test_admin_can_view_stock_card(): void
    {
        $admin = $this->userWithRole('admin');
        [$item, $warehouse] = $this->itemAndWarehouse();
        $stock = Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);

        $response = $this->actingAs($admin)->get(route('stocks.show', $stock));

        $response->assertOk();
        $response->assertSee('Kartu Stok');
        $response->assertSee('Benih Padi Ciherang ES');
    }

    public function test_guest_role_cannot_access_stocks(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('stocks.index'));

        $response->assertForbidden();
    }

    private function itemAndWarehouse(): array
    {
        $item = Item::create([
            'code' => 'BRG001',
            'name' => 'Benih Padi Ciherang ES',
            'category' => 'benih',
            'unit' => 'kg',
            'minimum_stock' => 500,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Benih Utama',
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
