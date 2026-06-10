<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_warehouses(): void
    {
        $admin = $this->userWithRole('admin');

        Warehouse::create([
            'code' => 'GDG001',
            'name' => 'Gudang Benih Utama',
            'person_in_charge' => 'Arif Setiawan',
            'phone' => '083100000001',
            'capacity_kg' => 25000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.warehouses.index'));

        $response->assertOk();
        $response->assertSee('Gudang Benih Utama');
    }

    public function test_editor_can_create_warehouse(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.warehouses.store'), [
            'code' => 'GDG002',
            'name' => 'Gudang Bahan Baku',
            'person_in_charge' => 'Lilis Suryani',
            'phone' => '083100000002',
            'address' => 'Area Pengeringan Blok A',
            'capacity_kg' => 40000,
            'notes' => 'Penyimpanan gabah calon benih.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.warehouses.index'));
        $this->assertDatabaseHas('warehouses', [
            'code' => 'GDG002',
            'name' => 'Gudang Bahan Baku',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_warehouse(): void
    {
        $admin = $this->userWithRole('admin');
        $warehouse = Warehouse::create([
            'code' => 'GDG003',
            'name' => 'Gudang Kemasan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.warehouses.update', $warehouse), [
            'code' => 'GDG003',
            'name' => 'Gudang Kemasan Update',
            'person_in_charge' => 'Dedi Kurniawan',
            'phone' => '083100000003',
            'capacity_kg' => 9000,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.warehouses.index'));
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => 'Gudang Kemasan Update',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_warehouse(): void
    {
        $admin = $this->userWithRole('admin');
        $warehouse = Warehouse::create([
            'code' => 'GDG004',
            'name' => 'Gudang Transit',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.warehouses.destroy', $warehouse));

        $response->assertRedirect(route('master.warehouses.index'));
        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_guest_role_cannot_access_warehouses(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.warehouses.index'));

        $response->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
