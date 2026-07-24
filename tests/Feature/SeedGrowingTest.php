<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\Item;
use App\Models\Land;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\SeedGrowingInspection;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeedGrowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_seed_growings(): void
    {
        $admin = $this->userWithRole('admin');
        $data = $this->masterData();

        SeedGrowing::create($this->payload($data));

        $response = $this->actingAs($admin)->get(route('seed-growings.index'));

        $response->assertOk();
        $response->assertSee('LP-001');
        $response->assertSee($data['farmer']->name);
    }

    public function test_editor_can_create_seed_growing(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        $response = $this->actingAs($editor)->post(route('seed-growings.store'), $this->payload($data, [
            'number' => null,
            'field_number' => 'lp-002',
            'lot_number' => 'lot-a',
        ]));

        $seedGrowing = SeedGrowing::first();

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growings', [
            'field_number' => 'LP-002',
            'lot_number' => 'LOT-A',
            'season_year' => 2026,
            'created_by' => $editor->id,
        ]);
        $this->assertDatabaseCount('seed_growing_inspections', 4);
        $this->assertDatabaseHas('seed_growing_inspections', [
            'seed_growing_id' => $seedGrowing->id,
            'stage' => 'pendahuluan',
            'planned_date' => '2026-06-25',
            'status' => 'terjadwal',
        ]);
        $this->assertDatabaseHas('seed_growing_inspections', [
            'seed_growing_id' => $seedGrowing->id,
            'stage' => 'pl3',
            'planned_date' => '2026-09-13',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $data['stock']->id,
            'quantity' => 50,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $data['stock']->id,
            'item_id' => $data['sourceSeedItem']->id,
            'warehouse_id' => $data['warehouse']->id,
            'type' => 'seed_growing_usage',
            'quantity_out' => 25,
            'balance_after' => 50,
            'reference_type' => 'seed_growing',
            'reference_id' => $seedGrowing->id,
        ]);
    }

    public function test_create_page_contains_land_area_for_auto_fill(): void
    {
        $editor = $this->userWithRole('editor');
        $this->masterData();

        $response = $this->actingAs($editor)->get(route('seed-growings.create'));

        $response->assertOk();
        $response->assertSee('data-area-size="12.00"', false);
    }

    public function test_create_page_contains_source_seed_stock_for_auto_quantity(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        $response = $this->actingAs($editor)->get(route('seed-growings.create'));

        $response->assertOk();
        $response->assertSee($data['sourceSeedItem']->id, false);
        $response->assertSee($data['warehouse']->id, false);
        $response->assertSee('75.00', false);
    }

    public function test_source_seed_quantity_cannot_exceed_available_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        Stock::where('item_id', $data['sourceSeedItem']->id)
            ->where('warehouse_id', $data['warehouse']->id)
            ->update(['quantity' => 10]);

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.create'))
            ->post(route('seed-growings.store'), $this->payload($data, [
                'source_seed_quantity' => 10.01,
            ]));

        $response->assertRedirect(route('seed-growings.create'));
        $response->assertSessionHasErrors('source_seed_quantity');
        $this->assertDatabaseCount('seed_growings', 0);
    }

    public function test_source_seed_item_must_match_selected_variety(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $otherVariety = RiceVariety::create([
            'code' => 'INP',
            'name' => 'Inpari',
            'is_active' => true,
        ]);
        $otherSourceSeedItem = Item::create([
            'code' => 'BNH002',
            'name' => 'Benih Sumber Inpari FS',
            'category' => 'benih',
            'material_state' => 'benih_jadi',
            'unit' => 'kg',
            'rice_variety_id' => $otherVariety->id,
            'seed_class_id' => $data['seedClass']->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        Stock::create([
            'item_id' => $otherSourceSeedItem->id,
            'warehouse_id' => $data['warehouse']->id,
            'quantity' => 100,
        ]);

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.create'))
            ->post(route('seed-growings.store'), $this->payload($data, [
                'source_seed_item_id' => $otherSourceSeedItem->id,
                'source_seed_quantity' => 25,
            ]));

        $response->assertRedirect(route('seed-growings.create'));
        $response->assertSessionHasErrors('source_seed_item_id');
        $this->assertDatabaseCount('seed_growings', 0);
    }

    public function test_source_seed_item_without_variety_can_be_used_for_selected_variety(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $unassignedSourceSeedItem = Item::create([
            'code' => 'BNH999',
            'name' => 'Benih Jadi Pembelian',
            'category' => 'benih',
            'material_state' => 'benih_jadi',
            'unit' => 'kg',
            'rice_variety_id' => null,
            'seed_class_id' => $data['seedClass']->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $unassignedStock = Stock::create([
            'item_id' => $unassignedSourceSeedItem->id,
            'warehouse_id' => $data['warehouse']->id,
            'quantity' => 40,
        ]);

        $createResponse = $this->actingAs($editor)->get(route('seed-growings.create'));

        $createResponse->assertOk();
        $createResponse->assertSee('BNH999 - Benih Jadi Pembelian - Varietas belum diset', false);

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.create'))
            ->post(route('seed-growings.store'), $this->payload($data, [
                'number' => 'PKR-20260706-UNASSIGNED',
                'field_number' => 'LP-UNASSIGNED',
                'source_seed_item_id' => $unassignedSourceSeedItem->id,
                'source_seed_quantity' => 25,
            ]));

        $seedGrowing = SeedGrowing::where('number', 'PKR-20260706-UNASSIGNED')->first();

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'rice_variety_id' => $data['riceVariety']->id,
            'source_seed_item_id' => $unassignedSourceSeedItem->id,
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $unassignedStock->id,
            'quantity' => 15,
        ]);
    }

    public function test_editor_can_update_seed_growing_inspection_cost_and_dates(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'draft',
        ]));
        $inspection = SeedGrowingInspection::create([
            'seed_growing_id' => $seedGrowing->id,
            'stage' => 'pl1',
            'planned_date' => '2026-07-25',
            'status' => 'terjadwal',
            'cost' => 0,
        ]);

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.inspections.update', [$seedGrowing, $inspection]),
            [
                'planned_date' => '2026-07-26',
                'actual_date' => '2026-07-27',
                'cost' => 'Rp 200.000',
                'status' => 'selesai',
                'notes' => 'Pemeriksaan selesai.',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growing_inspections', [
            'id' => $inspection->id,
            'planned_date' => '2026-07-26',
            'actual_date' => '2026-07-27',
            'cost' => 200000,
            'status' => 'selesai',
            'notes' => 'Pemeriksaan selesai.',
            'updated_by' => $editor->id,
        ]);
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'status' => 'berjalan',
        ]);
    }

    public function test_inspection_actual_date_defaults_to_planned_date_when_empty(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'draft',
        ]));
        $inspection = SeedGrowingInspection::create([
            'seed_growing_id' => $seedGrowing->id,
            'stage' => 'pl2',
            'planned_date' => '2026-08-24',
            'status' => 'terjadwal',
            'cost' => 0,
        ]);

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.inspections.update', [$seedGrowing, $inspection]),
            [
                'planned_date' => '2026-08-25',
                'actual_date' => null,
                'cost' => 0,
                'status' => 'selesai',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growing_inspections', [
            'id' => $inspection->id,
            'planned_date' => '2026-08-25',
            'actual_date' => '2026-08-25',
            'status' => 'selesai',
        ]);
    }

    public function test_editor_can_cancel_seed_growing_status(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'draft',
        ]));
        $data['stock']->update(['quantity' => 50]);

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.status.update', $seedGrowing),
            ['status' => 'batal']
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'status' => 'batal',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $data['stock']->id,
            'quantity' => 75,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $data['stock']->id,
            'item_id' => $data['sourceSeedItem']->id,
            'warehouse_id' => $data['warehouse']->id,
            'type' => 'seed_growing_cancel',
            'quantity_in' => 25,
            'quantity_out' => 0,
            'balance_after' => 75,
            'reference_type' => 'seed_growing',
            'reference_id' => $seedGrowing->id,
            'created_by' => $editor->id,
        ]);
    }

    public function test_seed_growing_cannot_be_cancelled_after_running(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'berjalan',
            'lot_number' => 'LOT-PANEN-001',
        ]));

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.status.update', $seedGrowing),
            ['status' => 'batal']
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'status' => 'berjalan',
        ]);
    }

    public function test_cancelled_seed_growing_cannot_update_inspection(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'batal',
        ]));
        $inspection = SeedGrowingInspection::create([
            'seed_growing_id' => $seedGrowing->id,
            'stage' => 'pl1',
            'planned_date' => '2026-07-25',
            'status' => 'terjadwal',
            'cost' => 0,
        ]);

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.inspections.update', [$seedGrowing, $inspection]),
            [
                'planned_date' => '2026-07-26',
                'actual_date' => '2026-07-27',
                'cost' => 'Rp 200.000',
                'status' => 'selesai',
                'notes' => 'Tidak boleh tersimpan.',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('seed_growing_inspections', [
            'id' => $inspection->id,
            'planned_date' => '2026-07-25',
            'actual_date' => null,
            'cost' => 0,
            'status' => 'terjadwal',
            'notes' => null,
        ]);
    }

    public function test_cancelled_seed_growing_cannot_save_harvest(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'batal',
        ]));

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.harvest.update', $seedGrowing),
            [
                'harvest_item_id' => $data['harvestItem']->id,
                'harvest_warehouse_id' => $data['warehouse']->id,
                'harvest_date' => '2026-09-24',
                'harvested_quantity' => 8000,
                'unit' => 'kg',
                'material_state' => 'basah',
                'status' => 'panen',
                'notes' => 'Tidak boleh tersimpan.',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('seed_growing_harvests', [
            'seed_growing_id' => $seedGrowing->id,
        ]);
        $this->assertDatabaseMissing('stock_movements', [
            'type' => 'seed_growing_harvest',
            'reference_id' => $seedGrowing->id,
        ]);
    }

    public function test_editor_can_save_harvest_and_status_becomes_panen(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'berjalan',
            'lot_number' => 'LOT-PANEN-001',
        ]));

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.harvest.update', $seedGrowing),
            [
                'harvest_item_id' => $data['harvestItem']->id,
                'harvest_warehouse_id' => $data['warehouse']->id,
                'harvest_date' => '2026-09-24',
                'harvested_quantity' => 8000,
                'unit' => 'kg',
                'material_state' => 'basah',
                'status' => 'panen',
                'notes' => 'Panen gabah calon benih.',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growing_harvests', [
            'seed_growing_id' => $seedGrowing->id,
            'harvest_item_id' => $data['harvestItem']->id,
            'harvest_warehouse_id' => $data['warehouse']->id,
            'harvest_date' => '2026-09-24',
            'harvested_quantity' => 8000,
            'unit' => 'kg',
            'material_state' => 'basah',
            'status' => 'panen',
            'notes' => 'Panen gabah calon benih.',
            'created_by' => $editor->id,
            'updated_by' => $editor->id,
        ]);
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'harvest_date' => '2026-09-24',
            'status' => 'panen',
        ]);
        $this->assertDatabaseHas('stocks', [
            'item_id' => $data['harvestItem']->id,
            'warehouse_id' => $data['warehouse']->id,
            'lot_number' => 'LOT-PANEN-001',
            'quantity' => 8000,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $data['harvestItem']->id,
            'warehouse_id' => $data['warehouse']->id,
            'type' => 'seed_growing_harvest',
            'quantity_in' => 8000,
            'reference_type' => 'seed_growing_harvest',
            'reference_id' => $seedGrowing->id,
        ]);
    }

    public function test_harvest_finished_sets_seed_growing_status_to_selesai(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'panen',
        ]));

        $response = $this->actingAs($editor)->patch(
            route('seed-growings.harvest.update', $seedGrowing),
            [
                'harvest_item_id' => $data['harvestItem']->id,
                'harvest_warehouse_id' => $data['warehouse']->id,
                'harvest_date' => '2026-09-24',
                'harvested_quantity' => 7500,
                'unit' => 'kg',
                'material_state' => 'kering',
                'status' => 'selesai',
            ]
        );

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $this->assertDatabaseHas('seed_growings', [
            'id' => $seedGrowing->id,
            'status' => 'selesai',
        ]);
    }

    public function test_harvest_item_must_match_seed_growing_variety(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $otherVariety = RiceVariety::create([
            'code' => 'CIH',
            'name' => 'Ciherang',
            'is_active' => true,
        ]);
        $otherHarvestItem = Item::create([
            'code' => 'GCB002',
            'name' => 'Gabah Calon Benih Ciherang',
            'category' => 'gabah',
            'material_state' => 'basah',
            'unit' => 'kg',
            'rice_variety_id' => $otherVariety->id,
            'seed_class_id' => $data['seedClass']->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $seedGrowing = SeedGrowing::create($this->payload($data, [
            'status' => 'berjalan',
        ]));

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.show', $seedGrowing))
            ->patch(route('seed-growings.harvest.update', $seedGrowing), [
                'harvest_item_id' => $otherHarvestItem->id,
                'harvest_warehouse_id' => $data['warehouse']->id,
                'harvest_date' => '2026-09-24',
                'harvested_quantity' => 8000,
                'unit' => 'kg',
                'material_state' => 'basah',
                'status' => 'panen',
            ]);

        $response->assertRedirect(route('seed-growings.show', $seedGrowing));
        $response->assertSessionHasErrors('harvest_item_id');
        $this->assertDatabaseMissing('seed_growing_harvests', [
            'seed_growing_id' => $seedGrowing->id,
        ]);
    }

    public function test_show_page_only_lists_harvest_items_from_seed_growing_variety(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $otherVariety = RiceVariety::create([
            'code' => 'MRK',
            'name' => 'Mekongga',
            'is_active' => true,
        ]);
        Item::create([
            'code' => 'GCB003',
            'name' => 'Gabah Calon Benih Mekongga',
            'category' => 'gabah',
            'material_state' => 'basah',
            'unit' => 'kg',
            'rice_variety_id' => $otherVariety->id,
            'seed_class_id' => $data['seedClass']->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $seedGrowing = SeedGrowing::create($this->payload($data));

        $response = $this->actingAs($editor)->get(route('seed-growings.show', $seedGrowing));

        $response->assertOk();
        $response->assertSee('Gabah Calon Benih IR64');
        $response->assertDontSee('Gabah Calon Benih Mekongga');
    }

    public function test_field_area_cannot_exceed_five_hectares(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.create'))
            ->post(route('seed-growings.store'), $this->payload($data, [
                'field_area' => 5.01,
            ]));

        $response->assertRedirect(route('seed-growings.create'));
        $response->assertSessionHasErrors('field_area');
        $this->assertDatabaseCount('seed_growings', 0);
    }

    public function test_field_number_must_be_unique_per_land_and_season_year(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        SeedGrowing::create($this->payload($data, [
            'field_number' => 'LP-003',
        ]));

        $response = $this->actingAs($editor)
            ->from(route('seed-growings.create'))
            ->post(route('seed-growings.store'), $this->payload($data, [
                'number' => 'PKR-20260706-TEST',
                'field_number' => 'LP-003',
            ]));

        $response->assertRedirect(route('seed-growings.create'));
        $response->assertSessionHasErrors('field_number');
        $this->assertDatabaseCount('seed_growings', 1);
    }

    public function test_guest_role_cannot_access_seed_growings(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('seed-growings.index'));

        $response->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function masterData(): array
    {
        $farmer = Farmer::create([
            'code' => 'PTR001',
            'name' => 'Budi Santoso',
            'is_active' => true,
        ]);
        $land = Land::create([
            'farmer_id' => $farmer->id,
            'code' => 'LHN001',
            'name' => 'Sawah Blok Timur',
            'area_size' => 12,
            'is_active' => true,
        ]);
        $riceVariety = RiceVariety::create([
            'code' => 'IR64',
            'name' => 'IR 64',
            'is_active' => true,
        ]);
        $seedClass = SeedClass::create([
            'code' => 'FS',
            'name' => 'Foundation Seed',
            'is_active' => true,
        ]);
        $sourceSeedItem = Item::create([
            'code' => 'BNH001',
            'name' => 'Benih Sumber IR64 FS',
            'category' => 'benih',
            'material_state' => 'benih_jadi',
            'unit' => 'kg',
            'rice_variety_id' => $riceVariety->id,
            'seed_class_id' => $seedClass->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $harvestItem = Item::create([
            'code' => 'GCB001',
            'name' => 'Gabah Calon Benih IR64',
            'category' => 'gabah',
            'material_state' => 'basah',
            'unit' => 'kg',
            'rice_variety_id' => $riceVariety->id,
            'seed_class_id' => $seedClass->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Benih',
            'is_active' => true,
        ]);
        $stock = Stock::create([
            'item_id' => $sourceSeedItem->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 75,
        ]);

        return [
            'farmer' => $farmer,
            'land' => $land,
            'riceVariety' => $riceVariety,
            'seedClass' => $seedClass,
            'sourceSeedItem' => $sourceSeedItem,
            'harvestItem' => $harvestItem,
            'warehouse' => $warehouse,
            'stock' => $stock,
        ];
    }

    private function payload(array $data, array $override = []): array
    {
        return [
            'number' => 'PKR-20260706-0001',
            'farmer_id' => $data['farmer']->id,
            'land_id' => $data['land']->id,
            'field_number' => 'LP-001',
            'lot_number' => null,
            'season_year' => 2026,
            'rice_variety_id' => $data['riceVariety']->id,
            'seed_class_id' => $data['seedClass']->id,
            'source_seed_item_id' => $data['sourceSeedItem']->id,
            'source_seed_warehouse_id' => $data['warehouse']->id,
            'source_seed_quantity' => 25,
            'field_area' => 4.5,
            'sowing_date' => '2026-06-01',
            'planting_date' => '2026-06-10',
            'preliminary_date' => '2026-06-25',
            'field_inspection_1_date' => '2026-07-25',
            'field_inspection_2_date' => '2026-08-24',
            'field_inspection_3_date' => '2026-09-13',
            'harvest_date' => '2026-09-23',
            'status' => 'berjalan',
            'notes' => 'Musim tanam utama.',
            ...$override,
        ];
    }
}
