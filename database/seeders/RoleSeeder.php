<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermissionCatalog::syncDefaultPermissionsAndRoles();

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $guestRole = Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('123456')]
        );
        $admin->syncRoles([$superAdminRole, $adminRole]);

        $editor = User::updateOrCreate(
            ['email' => 'editor@mail.com'],
            ['name' => 'Editor', 'password' => bcrypt('123456')]
        );
        $editor->syncRoles([$editorRole]);

        $guest = User::updateOrCreate(
            ['email' => 'guest@mail.com'],
            ['name' => 'Guest', 'password' => bcrypt('123456')]
        );
        $guest->syncRoles([$guestRole]);
    }
}
