<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FarmerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_farmers(): void
    {
        $admin = $this->userWithRole('admin');

        Farmer::create([
            'code' => 'PTR001',
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.farmers.index'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
    }

    public function test_editor_can_create_farmer(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.farmers.store'), [
            'code' => 'PTR002',
            'name' => 'Siti Aminah',
            'phone' => '082233445566',
            'identity_number' => '3201010101010001',
            'address' => 'Desa Sukamaju',
            'notes' => 'Petani mitra aktif.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.farmers.index'));
        $this->assertDatabaseHas('farmers', [
            'code' => 'PTR002',
            'name' => 'Siti Aminah',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_farmer(): void
    {
        $admin = $this->userWithRole('admin');
        $farmer = Farmer::create([
            'code' => 'PTR003',
            'name' => 'Joko Prasetyo',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.farmers.update', $farmer), [
            'code' => 'PTR003',
            'name' => 'Joko Prasetyo Update',
            'phone' => '083344556677',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.farmers.index'));
        $this->assertDatabaseHas('farmers', [
            'id' => $farmer->id,
            'name' => 'Joko Prasetyo Update',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_farmer(): void
    {
        $admin = $this->userWithRole('admin');
        $farmer = Farmer::create([
            'code' => 'PTR004',
            'name' => 'Dewi Lestari',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.farmers.destroy', $farmer));

        $response->assertRedirect(route('master.farmers.index'));
        $this->assertDatabaseMissing('farmers', [
            'id' => $farmer->id,
        ]);
    }

    public function test_guest_role_cannot_access_farmers(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.farmers.index'));

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
