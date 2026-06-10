<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $guestRole = Role::firstOrCreate(['name' => 'guest']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            ['name' => 'Admin', 'password' => bcrypt('123456')]
        );
        $admin->syncRoles([$adminRole]);

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
