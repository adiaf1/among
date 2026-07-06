<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\Land;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_lands(): void
    {
        $admin = $this->userWithRole('admin');
        $farmer = $this->farmer();

        Land::create([
            'farmer_id' => $farmer->id,
            'code' => 'LHN001',
            'name' => 'Sawah Blok Timur',
            'area_size' => 2.5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.lands.index'));

        $response->assertOk();
        $response->assertSee('Sawah Blok Timur');
        $response->assertSee($farmer->name);
    }

    public function test_editor_can_create_land(): void
    {
        $editor = $this->userWithRole('editor');
        $farmer = $this->farmer();

        $response = $this->actingAs($editor)->post(route('master.lands.store'), [
            'farmer_id' => $farmer->id,
            'code' => 'LHN002',
            'name' => 'Sawah Blok Barat',
            'area_size' => 1.75,
            'location' => 'Desa Sukamaju',
            'latitude' => -6.5601234,
            'longitude' => 107.7601234,
            'soil_type' => 'Aluvial',
            'irrigation_type' => 'Teknis',
            'ownership_status' => 'Milik sendiri',
            'certification_status' => 'layak',
            'notes' => 'Lahan siap tanam.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.lands.index'));
        $this->assertDatabaseHas('lands', [
            'farmer_id' => $farmer->id,
            'code' => 'LHN002',
            'name' => 'Sawah Blok Barat',
            'latitude' => -6.5601234,
            'longitude' => 107.7601234,
            'certification_status' => 'layak',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_land(): void
    {
        $admin = $this->userWithRole('admin');
        $farmer = $this->farmer();
        $land = Land::create([
            'farmer_id' => $farmer->id,
            'code' => 'LHN003',
            'name' => 'Sawah Lama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.lands.update', $land), [
            'farmer_id' => $farmer->id,
            'code' => 'LHN003',
            'name' => 'Sawah Baru',
            'area_size' => 3.25,
            'latitude' => -6.4400000,
            'longitude' => 107.7700000,
            'certification_status' => 'perlu_perbaikan',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.lands.index'));
        $this->assertDatabaseHas('lands', [
            'id' => $land->id,
            'name' => 'Sawah Baru',
            'certification_status' => 'perlu_perbaikan',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_land(): void
    {
        $admin = $this->userWithRole('admin');
        $farmer = $this->farmer();
        $land = Land::create([
            'farmer_id' => $farmer->id,
            'code' => 'LHN004',
            'name' => 'Sawah Selatan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.lands.destroy', $land));

        $response->assertRedirect(route('master.lands.index'));
        $this->assertDatabaseMissing('lands', [
            'id' => $land->id,
        ]);
    }

    public function test_guest_role_cannot_access_lands(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.lands.index'));

        $response->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function farmer(): Farmer
    {
        return Farmer::create([
            'code' => 'PTR001',
            'name' => 'Budi Santoso',
            'is_active' => true,
        ]);
    }
}
