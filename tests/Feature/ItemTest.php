<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\RiceVariety;
use App\Models\SeedClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_items(): void
    {
        $admin = $this->userWithRole('admin');

        Item::create([
            'code' => 'BRG001',
            'name' => 'Benih Padi Ciherang ES',
            'category' => 'benih',
            'unit' => 'kg',
            'minimum_stock' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('master.items.index'));

        $response->assertOk();
        $response->assertSee('Benih Padi Ciherang ES');
    }

    public function test_editor_can_create_item(): void
    {
        $editor = $this->userWithRole('editor');
        $riceVariety = RiceVariety::create([
            'code' => 'VRT001',
            'name' => 'Ciherang',
            'is_active' => true,
        ]);
        $seedClass = SeedClass::create([
            'code' => 'KB001',
            'name' => 'Benih Sebar',
            'is_active' => true,
        ]);

        $response = $this->actingAs($editor)->post(route('master.items.store'), [
            'code' => 'BRG002',
            'name' => 'Benih Padi Ciherang ES',
            'category' => 'benih',
            'unit' => 'kg',
            'rice_variety_id' => $riceVariety->id,
            'seed_class_id' => $seedClass->id,
            'minimum_stock' => 500,
            'description' => 'Benih siap jual.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.items.index'));
        $this->assertDatabaseHas('items', [
            'code' => 'BRG002',
            'name' => 'Benih Padi Ciherang ES',
            'category' => 'benih',
            'unit' => 'kg',
            'rice_variety_id' => $riceVariety->id,
            'seed_class_id' => $seedClass->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_item(): void
    {
        $admin = $this->userWithRole('admin');
        $item = Item::create([
            'code' => 'BRG003',
            'name' => 'Karung Kemasan 5 Kg',
            'category' => 'kemasan',
            'unit' => 'pcs',
            'minimum_stock' => 200,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('master.items.update', $item), [
            'code' => 'BRG003',
            'name' => 'Karung Kemasan 10 Kg',
            'category' => 'kemasan',
            'unit' => 'pcs',
            'minimum_stock' => 300,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('master.items.index'));
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Karung Kemasan 10 Kg',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_item(): void
    {
        $admin = $this->userWithRole('admin');
        $item = Item::create([
            'code' => 'BRG004',
            'name' => 'Label Sertifikasi Benih',
            'category' => 'kemasan',
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.items.destroy', $item));

        $response->assertRedirect(route('master.items.index'));
        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    }

    public function test_guest_role_cannot_access_items(): void
    {
        $guest = $this->userWithRole('guest');

        $response = $this->actingAs($guest)->get(route('master.items.index'));

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
