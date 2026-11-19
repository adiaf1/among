<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_operational_widgets_and_hpp_summary(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Operasional');
        $response->assertSee('Peringatan Stok Minimum');
        $response->assertSee('Jadwal Pemeriksaan Lapang Terdekat');
        $response->assertSee('Ringkasan Kalkulasi HPP per Kg Benih Jadi');
    }

    public function test_custom_permission_group_only_sees_allowed_widgets(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $salesRole = Role::create(['name' => 'staf-penjualan', 'guard_name' => 'web']);
        $salesRole->syncPermissions(['sales.manage']);

        $salesUser = User::factory()->create();
        $salesUser->assignRole($salesRole);

        $response = $this->actingAs($salesUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Penjualan Bulan Ini');
        $response->assertSee('Penjualan Belum Lunas (Piutang)');
        $response->assertDontSee('Peringatan Stok Minimum');
        $response->assertDontSee('Ringkasan Kalkulasi HPP per Kg Benih Jadi');
    }

    public function test_guest_user_without_module_permissions_does_not_get_forbidden_on_dashboard(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $guest = User::factory()->create();
        $guest->assignRole('guest');

        $response = $this->actingAs($guest)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Akun Anda Aktif');
    }
}
