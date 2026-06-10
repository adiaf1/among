<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_stock_transfers(): void
    {
        $admin = $this->userWithRole('admin');
        [$sourceWarehouse, $destinationWarehouse] = $this->warehouses();

        StockTransfer::create([
            'number' => 'MT-001',
            'transfer_date' => '2026-06-10',
            'source_warehouse_id' => $sourceWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
        ]);

        $response = $this->actingAs($admin)->get(route('stock-transfers.index'));

        $response->assertOk();
        $response->assertSee('MT-001');
        $response->assertSee('Gudang Benih Utama');
    }

    public function test_admin_can_render_create_stock_transfer_page(): void
    {
        $admin = $this->userWithRole('admin');
        $this->item();
        $this->warehouses();

        $response = $this->actingAs($admin)->get(route('stock-transfers.create'));

        $response->assertOk();
        $response->assertSee('Tambah Mutasi Stok');
        $response->assertSee('Tambah Baris');
    }

    public function test_editor_can_create_stock_transfer_and_move_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $item = $this->item();
        [$sourceWarehouse, $destinationWarehouse] = $this->warehouses();

        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 1000,
        ]);

        $response = $this->actingAs($editor)->post(route('stock-transfers.store'), [
            'number' => 'MT-002',
            'transfer_date' => '2026-06-10',
            'source_warehouse_id' => $sourceWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'notes' => 'Pindah stok ke gudang transit.',
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 300,
                ],
            ],
        ]);

        $stockTransfer = StockTransfer::first();

        $response->assertRedirect(route('stock-transfers.show', $stockTransfer));
        $this->assertDatabaseHas('stock_transfers', [
            'number' => 'MT-002',
            'source_warehouse_id' => $sourceWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
        ]);
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 700,
        ]);
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $destinationWarehouse->id,
            'quantity' => 300,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'transfer_out',
            'quantity_out' => 300,
            'balance_after' => 700,
            'reference_number' => 'MT-002',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'transfer_in',
            'quantity_in' => 300,
            'balance_after' => 300,
            'reference_number' => 'MT-002',
        ]);
    }

    public function test_stock_transfer_rejects_insufficient_stock(): void
    {
        $admin = $this->userWithRole('admin');
        $item = $this->item();
        [$sourceWarehouse, $destinationWarehouse] = $this->warehouses();

        Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($admin)->post(route('stock-transfers.store'), [
            'number' => 'MT-003',
            'transfer_date' => '2026-06-10',
            'source_warehouse_id' => $sourceWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 300,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('stock_transfers', [
            'number' => 'MT-003',
        ]);
        $this->assertDatabaseHas('stocks', [
            'item_id' => $item->id,
            'warehouse_id' => $sourceWarehouse->id,
            'quantity' => 100,
        ]);
    }

    public function test_admin_can_view_stock_transfer_detail(): void
    {
        $admin = $this->userWithRole('admin');
        [$sourceWarehouse, $destinationWarehouse] = $this->warehouses();
        $stockTransfer = StockTransfer::create([
            'number' => 'MT-004',
            'transfer_date' => '2026-06-10',
            'source_warehouse_id' => $sourceWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
        ]);

        $response = $this->actingAs($admin)->get(route('stock-transfers.show', $stockTransfer));

        $response->assertOk();
        $response->assertSee('Detail Mutasi Stok');
        $response->assertSee('MT-004');
    }

    public function test_guest_role_cannot_access_stock_transfers(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('stock-transfers.index'));

        $response->assertForbidden();
    }

    private function item(): Item
    {
        return Item::create([
            'code' => 'BRG001',
            'name' => 'Benih Padi Ciherang ES',
            'category' => 'benih',
            'unit' => 'kg',
            'minimum_stock' => 500,
            'is_active' => true,
        ]);
    }

    private function warehouses(): array
    {
        $sourceWarehouse = Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Benih Utama',
            'is_active' => true,
        ]);
        $destinationWarehouse = Warehouse::create([
            'code' => 'GDG002',
            'name' => 'Gudang Transit',
            'is_active' => true,
        ]);

        return [$sourceWarehouse, $destinationWarehouse];
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
