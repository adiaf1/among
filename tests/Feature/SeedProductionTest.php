<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Farmer;
use App\Models\Land;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\SeedGrowing;
use App\Models\SeedProduction;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeedProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_seed_production_from_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        $response = $this->actingAs($editor)->post(route('seed-productions.store'), [
            'number' => null,
            'production_date' => '2026-09-25',
            'lot_number' => 'lot-ir64-001',
            'rice_variety_id' => $data['riceVariety']->id,
            'seed_class_id' => $data['seedClass']->id,
            'output_warehouse_id' => $data['warehouse']->id,
            'target_quantity' => 700,
            'unit' => 'kg',
            'notes' => 'Produksi awal.',
            'inputs' => [
                [
                    'stock_id' => $data['stock']->id,
                    'role' => 'bahan_utama',
                    'quantity' => 800,
                    'notes' => 'Gabah calon benih.',
                ],
            ],
        ]);

        $seedProduction = SeedProduction::first();

        $response->assertRedirect(route('seed-productions.show', $seedProduction));
        $this->assertDatabaseHas('seed_productions', [
            'id' => $seedProduction->id,
            'lot_number' => 'LOT-IR64-001',
            'status' => 'proses',
            'created_by' => $editor->id,
        ]);
        $this->assertDatabaseHas('seed_production_inputs', [
            'seed_production_id' => $seedProduction->id,
            'stock_id' => $data['stock']->id,
            'item_id' => $data['item']->id,
            'warehouse_id' => $data['warehouse']->id,
            'role' => 'bahan_utama',
            'quantity' => 800,
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $data['stock']->id,
            'quantity' => 200,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $data['stock']->id,
            'item_id' => $data['item']->id,
            'warehouse_id' => $data['warehouse']->id,
            'type' => 'seed_production_input',
            'quantity_out' => 800,
            'balance_after' => 200,
            'reference_type' => 'seed_production',
            'reference_number' => $seedProduction->number,
        ]);
        $this->assertDatabaseCount('seed_production_steps', 9);
        $this->assertDatabaseHas('seed_production_steps', [
            'seed_production_id' => $seedProduction->id,
            'stage' => 'pengovenan',
            'sort_order' => 1,
            'planned_date' => '2026-09-25',
            'status' => 'terjadwal',
        ]);
        $this->assertDatabaseMissing('seed_production_steps', [
            'seed_production_id' => $seedProduction->id,
            'stage' => 'panen',
        ]);
        $this->assertDatabaseMissing('seed_production_steps', [
            'seed_production_id' => $seedProduction->id,
            'stage' => 'menunggu_hasil_lab',
        ]);
        $this->assertDatabaseHas('seed_production_steps', [
            'seed_production_id' => $seedProduction->id,
            'stage' => 'siap_salur',
            'planned_date' => '2026-10-04',
            'status' => 'terjadwal',
        ]);

        $this->actingAs($editor)
            ->get(route('seed-productions.show', $seedProduction))
            ->assertOk()
            ->assertSee('Detail Produksi Benih')
            ->assertSee('Pengovenan');
    }

    public function test_seed_production_quantity_cannot_exceed_stock(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();

        $response = $this->actingAs($editor)
            ->from(route('seed-productions.create'))
            ->post(route('seed-productions.store'), [
                'production_date' => '2026-09-25',
                'unit' => 'kg',
                'inputs' => [
                    [
                        'stock_id' => $data['stock']->id,
                        'role' => 'bahan_utama',
                        'quantity' => 1000.01,
                    ],
                ],
            ]);

        $response->assertRedirect(route('seed-productions.create'));
        $response->assertSessionHasErrors('inputs');
        $this->assertDatabaseCount('seed_productions', 0);
        $this->assertDatabaseHas('stocks', [
            'id' => $data['stock']->id,
            'quantity' => 1000,
        ]);
    }

    public function test_main_production_input_must_match_selected_variety(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
        $otherVariety = RiceVariety::create([
            'code' => 'CIH',
            'name' => 'Ciherang',
            'is_active' => true,
        ]);
        $otherItem = Item::create([
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
        $otherStock = Stock::create([
            'item_id' => $otherItem->id,
            'warehouse_id' => $data['warehouse']->id,
            'quantity' => 500,
        ]);

        $response = $this->actingAs($editor)
            ->from(route('seed-productions.create'))
            ->post(route('seed-productions.store'), [
                'production_date' => '2026-09-25',
                'rice_variety_id' => $data['riceVariety']->id,
                'seed_class_id' => $data['seedClass']->id,
                'unit' => 'kg',
                'inputs' => [
                    [
                        'stock_id' => $otherStock->id,
                        'role' => 'bahan_utama',
                        'quantity' => 100,
                    ],
                ],
            ]);

        $response->assertRedirect(route('seed-productions.create'));
        $response->assertSessionHasErrors('inputs');
        $this->assertDatabaseCount('seed_productions', 0);
        $this->assertDatabaseHas('stocks', [
            'id' => $otherStock->id,
            'quantity' => 500,
        ]);
    }

    public function test_editor_can_update_production_step_cost_per_kg(): void
    {
        $editor = $this->userWithRole('editor');
        $seedProduction = $this->createSeedProduction($editor);
        $step = $seedProduction->steps()->where('stage', 'pengovenan')->firstOrFail();

        $response = $this->actingAs($editor)->patch(
            route('seed-productions.steps.update', [$seedProduction, $step]),
            [
                'planned_date' => '2026-09-25',
                'actual_date' => '2026-09-26',
                'quantity' => 1000,
                'cost_per_kg' => 'Rp 150',
                'status' => 'selesai',
                'notes' => 'Oven gabah basah.',
            ]
        );

        $response->assertRedirect(route('seed-productions.show', $seedProduction));
        $this->assertDatabaseHas('seed_production_steps', [
            'id' => $step->id,
            'actual_date' => '2026-09-26',
            'quantity' => 1000,
            'cost_per_kg' => 150,
            'cost' => 150000,
            'status' => 'selesai',
            'notes' => 'Oven gabah basah.',
            'updated_by' => $editor->id,
        ]);
    }

    public function test_finished_ready_to_distribute_step_sets_production_status(): void
    {
        $editor = $this->userWithRole('editor');
        $seedProduction = $this->createSeedProduction($editor);
        $step = $seedProduction->steps()->where('stage', 'siap_salur')->firstOrFail();

        $response = $this->actingAs($editor)->patch(
            route('seed-productions.steps.update', [$seedProduction, $step]),
            [
                'planned_date' => '2026-10-04',
                'actual_date' => '2026-10-04',
                'quantity' => 900,
                'cost_per_kg' => 0,
                'status' => 'selesai',
            ]
        );

        $response->assertRedirect(route('seed-productions.show', $seedProduction));
        $this->assertDatabaseHas('seed_productions', [
            'id' => $seedProduction->id,
            'status' => 'siap_salur',
        ]);
    }

    public function test_create_page_can_choose_lot_from_harvested_seed_growing(): void
    {
        $editor = $this->userWithRole('editor');
        $data = $this->masterData();
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
        $sourceSeedItem = Item::create([
            'code' => 'BNH001',
            'name' => 'Benih Sumber IR64 SS',
            'category' => 'benih',
            'material_state' => 'benih_jadi',
            'unit' => 'kg',
            'rice_variety_id' => $data['riceVariety']->id,
            'seed_class_id' => $data['seedClass']->id,
            'minimum_stock' => 0,
            'is_active' => true,
        ]);
        $seedGrowing = SeedGrowing::create([
            'number' => 'PKR-20260706-LOT1',
            'farmer_id' => $farmer->id,
            'land_id' => $land->id,
            'field_number' => 'LP-LOT1',
            'lot_number' => 'LOT-PANEN-001',
            'season_year' => 2026,
            'rice_variety_id' => $data['riceVariety']->id,
            'seed_class_id' => $data['seedClass']->id,
            'source_seed_item_id' => $sourceSeedItem->id,
            'source_seed_warehouse_id' => $data['warehouse']->id,
            'source_seed_quantity' => 25,
            'field_area' => 4.5,
            'sowing_date' => '2026-06-01',
            'planting_date' => '2026-06-10',
            'harvest_date' => '2026-09-23',
            'status' => 'panen',
        ]);
        $seedGrowing->harvest()->create([
            'harvest_item_id' => $data['item']->id,
            'harvest_warehouse_id' => $data['warehouse']->id,
            'harvest_date' => '2026-09-23',
            'harvested_quantity' => 900,
            'unit' => 'kg',
            'material_state' => 'basah',
            'status' => 'panen',
        ]);

        $response = $this->actingAs($editor)->get(route('seed-productions.create'));

        $response->assertOk();
        $response->assertSee('list="harvest-lot-options"', false);
        $response->assertSee('LOT-PANEN-001');
        $response->assertSee($data['riceVariety']->id, false);
        $response->assertSee($data['seedClass']->id, false);
    }

    public function test_guest_role_cannot_access_seed_productions(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('seed-productions.index'));

        $response->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createSeedProduction(User $user): SeedProduction
    {
        $data = $this->masterData();

        $this->actingAs($user)->post(route('seed-productions.store'), [
            'production_date' => '2026-09-25',
            'lot_number' => 'LOT-IR64-001',
            'rice_variety_id' => $data['riceVariety']->id,
            'seed_class_id' => $data['seedClass']->id,
            'output_warehouse_id' => $data['warehouse']->id,
            'target_quantity' => 700,
            'unit' => 'kg',
            'inputs' => [
                [
                    'stock_id' => $data['stock']->id,
                    'role' => 'bahan_utama',
                    'quantity' => 800,
                ],
            ],
        ])->assertRedirect();

        return SeedProduction::with('steps')->firstOrFail();
    }

    private function masterData(): array
    {
        $riceVariety = RiceVariety::create([
            'code' => 'IR64',
            'name' => 'IR 64',
            'is_active' => true,
        ]);
        $seedClass = SeedClass::create([
            'code' => 'SS',
            'name' => 'Stock Seed',
            'is_active' => true,
        ]);
        $item = Item::create([
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
            'name' => 'Gudang Produksi',
            'is_active' => true,
        ]);
        $stock = Stock::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 1000,
        ]);

        return compact('riceVariety', 'seedClass', 'item', 'warehouse', 'stock');
    }
}
