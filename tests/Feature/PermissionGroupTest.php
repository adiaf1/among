<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_permission_group_and_assign_to_user(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->post(route('permission-groups.store'), [
            'name' => 'bagian-penjualan',
            'permissions' => [
                'sales.manage',
                'master.customers.manage',
            ],
        ]);

        $response->assertRedirect(route('permission-groups.index'));

        $role = Role::findByName('bagian-penjualan');
        $this->assertTrue($role->hasPermissionTo('sales.manage'));
        $this->assertTrue($role->hasPermissionTo('master.customers.manage'));
        $this->assertFalse($role->hasPermissionTo('purchases.manage'));

        $staffUser = User::factory()->create([
            'name' => 'Staf Penjualan',
            'email' => 'sales@mail.com',
        ]);

        $this->actingAs($superAdmin)->put(route('users.update', $staffUser), [
            'name' => 'Staf Penjualan',
            'email' => 'sales@mail.com',
            'roles' => ['bagian-penjualan'],
        ])->assertRedirect(route('users.index'));

        $staffUser->refresh();
        $this->assertTrue($staffUser->hasRole('bagian-penjualan'));

        // User dengan grup bagian-penjualan bisa membuka /sales dan /master/customers
        $this->actingAs($staffUser)->get(route('sales.index'))->assertOk();
        $this->actingAs($staffUser)->get(route('master.customers.index'))->assertOk();

        // Namun tidak bisa membuka /purchases atau /permission-groups
        $this->actingAs($staffUser)->get(route('purchases.index'))->assertForbidden();
        $this->actingAs($staffUser)->get(route('permission-groups.index'))->assertForbidden();
    }

    public function test_non_super_admin_cannot_manage_permission_groups(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor)->get(route('permission-groups.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('permission-groups.store'), [
            'name' => 'grup-ilegal',
            'permissions' => ['stocks.manage'],
        ])->assertForbidden();
    }
}
