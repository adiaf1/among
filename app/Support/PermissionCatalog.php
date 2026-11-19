<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionCatalog
{
    public const SUPER_ADMIN_ROLES = ['super-admin', 'admin'];

    public const PROTECTED_ROLES = ['super-admin', 'admin', 'editor', 'guest'];

    public static function groups(): array
    {
        return [
            'Master Data' => [
                'master.rice-varieties.manage' => 'Varietas Padi',
                'master.seed-classes.manage' => 'Kelas Benih',
                'master.farmers.manage' => 'Petani',
                'master.lands.manage' => 'Lahan Penangkaran',
                'master.suppliers.manage' => 'Supplier',
                'master.customers.manage' => 'Pelanggan',
                'master.warehouses.manage' => 'Gudang',
                'master.items.manage' => 'Barang',
            ],
            'Persediaan & Transaksi' => [
                'stocks.manage' => 'Stok & Penyesuaian Stok',
                'purchases.manage' => 'Pembelian Barang',
                'sales.manage' => 'Penjualan / Penyaluran Barang',
                'stock-transfers.manage' => 'Mutasi Stok Antar Gudang',
            ],
            'Penangkaran & Produksi Benih' => [
                'seed-growings.manage' => 'Penangkaran Benih, Pemeriksaan & Panen',
                'seed-productions.manage' => 'Produksi Benih, Uji Lab & Hasil Akhir',
            ],
            'Laporan & Keuangan' => [
                'reports.financial.view' => 'Lihat Ringkasan Keuangan & HPP Benih',
            ],
        ];
    }

    public static function flatPermissions(): array
    {
        $flat = [];
        foreach (self::groups() as $groupName => $permissions) {
            foreach ($permissions as $name => $label) {
                $flat[$name] = [
                    'name' => $name,
                    'label' => $label,
                    'group' => $groupName,
                ];
            }
        }

        return $flat;
    }

    public static function allPermissionNames(): array
    {
        return array_keys(self::flatPermissions());
    }

    public static function syncDefaultPermissionsAndRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allNames = self::allPermissionNames();

        foreach ($allNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);

        $superAdminRole->syncPermissions($allNames);
        $adminRole->syncPermissions($allNames);

        if ($editorRole->permissions()->count() === 0) {
            $editorRole->syncPermissions($allNames);
        }

        // Pastikan user yang memiliki role admin juga memiliki akses super-admin
        $adminUsers = User::role('admin')->get();
        foreach ($adminUsers as $adminUser) {
            if (! $adminUser->hasRole('super-admin')) {
                $adminUser->assignRole($superAdminRole);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
