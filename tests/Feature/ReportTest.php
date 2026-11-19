<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_all_report_pages_and_csv_export(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $routes = [
            'reports.stocks',
            'reports.purchases',
            'reports.sales',
            'reports.seed-growings',
            'reports.seed-productions',
            'reports.certifications',
            'reports.financial',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($superAdmin)->get(route($routeName))->assertOk();
            $this->actingAs($superAdmin)->get(route($routeName, ['export' => 'csv']))->assertOk();
        }
    }

    public function test_report_pages_enforce_module_and_financial_permissions(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $salesOnlyRole = Role::create(['name' => 'staf-penjualan-saja', 'guard_name' => 'web']);
        $salesOnlyRole->syncPermissions(['sales.manage']);

        $user = User::factory()->create();
        $user->assignRole($salesOnlyRole);

        $this->actingAs($user)->get(route('reports.sales'))->assertOk();
        $this->actingAs($user)->get(route('reports.stocks'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.financial'))->assertForbidden();
    }
}
