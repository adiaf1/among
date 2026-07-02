<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_suppliers(): void
    {
        $admin = $this->userWithRole('admin');

        Supplier::create([
            'code' => 'SUP001',
            'name' => 'CV Agro Makmur',
            'contact_person' => 'Rina Wulandari',
            'phone' => '081234560001',
            'email' => 'agromakmur@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.suppliers.index'));

        $response->assertOk();
        $response->assertSee('CV Agro Makmur');
    }

    public function test_editor_can_create_supplier(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.suppliers.store'), [
            'name' => 'Tani Jaya Sejahtera',
            'contact_person' => 'Hendra Saputra',
            'phone' => '081234560002',
            'email' => 'tanijaya@example.com',
            'address' => 'Jl. Benih Unggul No. 8',
            'notes' => 'Supplier pupuk dan pestisida.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP001',
            'name' => 'Tani Jaya Sejahtera',
            'is_active' => true,
        ]);
    }

    public function test_supplier_code_is_generated_from_latest_number(): void
    {
        $editor = $this->userWithRole('editor');

        Supplier::create([
            'code' => 'SUP005',
            'name' => 'Supplier Lama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($editor)->post(route('master.suppliers.store'), [
            'name' => 'Supplier Berikutnya',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP006',
            'name' => 'Supplier Berikutnya',
        ]);
    }

    public function test_supplier_created_from_purchase_redirects_back_to_purchase_create(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.suppliers.store'), [
            'name' => 'Supplier Baru',
            'contact_person' => 'Dina',
            'phone' => '081234560005',
            'return_to' => 'purchases.create',
            'is_active' => '1',
        ]);

        $supplier = Supplier::where('name', 'Supplier Baru')->first();

        $response->assertRedirect(route('purchases.create', [
            'source_type' => 'supplier',
            'supplier_id' => $supplier->id,
        ]));
    }

    public function test_admin_can_update_supplier(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = Supplier::create([
            'code' => 'SUP003',
            'name' => 'UD Sumber Tani',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.suppliers.update', $supplier), [
            'code' => 'SUP003',
            'name' => 'UD Sumber Tani Update',
            'contact_person' => 'Maya Kartika',
            'phone' => '081234560003',
            'email' => 'sumbertani@example.com',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'UD Sumber Tani Update',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_supplier(): void
    {
        $admin = $this->userWithRole('admin');
        $supplier = Supplier::create([
            'code' => 'SUP004',
            'name' => 'PT Prima Benih Nusantara',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.suppliers.destroy', $supplier));

        $response->assertRedirect(route('master.suppliers.index'));
        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_guest_role_cannot_access_suppliers(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.suppliers.index'));

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
