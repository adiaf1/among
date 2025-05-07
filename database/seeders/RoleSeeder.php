<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'guest']);

        // Buat user admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Buat user editor
        $editor = User::create([
            'name' => 'Editor',
            'email' => 'editor@mail.com',
            'password' => bcrypt('password'),
        ]);
        $editor->assignRole('editor');

        // Buat user guest
        $guest = User::create([
            'name' => 'Guest',
            'email' => 'guest@mail.com',
            'password' => bcrypt('password'),
        ]);
        $guest->assignRole('guest');
    }

}
