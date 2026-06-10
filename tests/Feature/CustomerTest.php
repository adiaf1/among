<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customers(): void
    {
        $admin = $this->userWithRole('admin');

        Customer::create([
            'code' => 'CST001',
            'name' => 'Kios Tani Subur',
            'customer_type' => 'kios',
            'contact_person' => 'Agus Riyanto',
            'phone' => '082100000001',
            'email' => 'tanisubur@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.customers.index'));

        $response->assertOk();
        $response->assertSee('Kios Tani Subur');
    }

    public function test_editor_can_create_customer(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.customers.store'), [
            'code' => 'CST002',
            'name' => 'CV Berkah Agro',
            'customer_type' => 'distributor',
            'contact_person' => 'Santi Permata',
            'phone' => '082100000002',
            'email' => 'berkahagro@example.com',
            'address' => 'Komplek Pergudangan Agro Blok C-2',
            'notes' => 'Distributor area kabupaten.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.customers.index'));
        $this->assertDatabaseHas('customers', [
            'code' => 'CST002',
            'name' => 'CV Berkah Agro',
            'customer_type' => 'distributor',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_customer(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = Customer::create([
            'code' => 'CST003',
            'name' => 'Budi Hartono',
            'customer_type' => 'perorangan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.customers.update', $customer), [
            'code' => 'CST003',
            'name' => 'Budi Hartono Update',
            'customer_type' => 'perorangan',
            'phone' => '082100000003',
            'email' => 'budi.update@example.com',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Budi Hartono Update',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_customer(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = Customer::create([
            'code' => 'CST004',
            'name' => 'Dinas Pertanian Kecamatan',
            'customer_type' => 'instansi',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.customers.destroy', $customer));

        $response->assertRedirect(route('master.customers.index'));
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_guest_role_cannot_access_customers(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.customers.index'));

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
