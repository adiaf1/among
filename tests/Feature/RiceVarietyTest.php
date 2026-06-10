<?php

namespace Tests\Feature;

use App\Models\RiceVariety;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiceVarietyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_rice_varieties(): void
    {
        $admin = $this->userWithRole('admin');

        RiceVariety::create([
            'code' => 'INP32',
            'name' => 'Inpari 32',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.rice-varieties.index'));

        $response->assertOk();
        $response->assertSee('Inpari 32');
    }

    public function test_editor_can_create_rice_variety(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.rice-varieties.store'), [
            'code' => 'CIH',
            'name' => 'Ciherang',
            'description' => 'Varietas padi populer.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.rice-varieties.index'));
        $this->assertDatabaseHas('rice_varieties', [
            'code' => 'CIH',
            'name' => 'Ciherang',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_rice_variety(): void
    {
        $admin = $this->userWithRole('admin');
        $riceVariety = RiceVariety::create([
            'code' => 'MKG',
            'name' => 'Mekongga',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.rice-varieties.update', $riceVariety), [
            'code' => 'MKG',
            'name' => 'Mekongga Baru',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.rice-varieties.index'));
        $this->assertDatabaseHas('rice_varieties', [
            'id' => $riceVariety->id,
            'name' => 'Mekongga Baru',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_rice_variety(): void
    {
        $admin = $this->userWithRole('admin');
        $riceVariety = RiceVariety::create([
            'code' => 'IR64',
            'name' => 'IR 64',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.rice-varieties.destroy', $riceVariety));

        $response->assertRedirect(route('master.rice-varieties.index'));
        $this->assertDatabaseMissing('rice_varieties', [
            'id' => $riceVariety->id,
        ]);
    }

    public function test_guest_role_cannot_access_rice_varieties(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.rice-varieties.index'));

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
