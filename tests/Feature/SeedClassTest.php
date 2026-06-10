<?php

namespace Tests\Feature;

use App\Models\SeedClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeedClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_seed_classes(): void
    {
        $admin = $this->userWithRole('admin');

        SeedClass::create([
            'code' => 'BS',
            'name' => 'Benih Penjenis',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.seed-classes.index'));

        $response->assertOk();
        $response->assertSee('Benih Penjenis');
    }

    public function test_editor_can_create_seed_class(): void
    {
        $editor = $this->userWithRole('editor');

        $response = $this->actingAs($editor)->post(route('master.seed-classes.store'), [
            'code' => 'FS',
            'name' => 'Benih Dasar',
            'description' => 'Kelas benih dasar.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.seed-classes.index'));
        $this->assertDatabaseHas('seed_classes', [
            'code' => 'FS',
            'name' => 'Benih Dasar',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_seed_class(): void
    {
        $admin = $this->userWithRole('admin');
        $seedClass = SeedClass::create([
            'code' => 'SS',
            'name' => 'Benih Pokok',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.seed-classes.update', $seedClass), [
            'code' => 'SS',
            'name' => 'Benih Pokok Update',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.seed-classes.index'));
        $this->assertDatabaseHas('seed_classes', [
            'id' => $seedClass->id,
            'name' => 'Benih Pokok Update',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_seed_class(): void
    {
        $admin = $this->userWithRole('admin');
        $seedClass = SeedClass::create([
            'code' => 'ES',
            'name' => 'Benih Sebar',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.seed-classes.destroy', $seedClass));

        $response->assertRedirect(route('master.seed-classes.index'));
        $this->assertDatabaseMissing('seed_classes', [
            'id' => $seedClass->id,
        ]);
    }

    public function test_guest_role_cannot_access_seed_classes(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.seed-classes.index'));

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
