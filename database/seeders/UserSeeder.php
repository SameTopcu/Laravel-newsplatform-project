<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Spatie Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $editorRole = Role::firstOrCreate(['name' => 'editör']);
        $yazarRole = Role::firstOrCreate(['name' => 'yazar']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@gundemtr.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );
        $admin->assignRole($adminRole);

        $editor = User::firstOrCreate(
            ['email' => 'editor@gundemtr.com'],
            [
                'name' => 'Editör',
                'password' => Hash::make('editor123'),
                'role' => 'editör',
            ]
        );
        $editor->assignRole($editorRole);

        $yazar1 = User::firstOrCreate(
            ['email' => 'yazar1@gundemtr.com'],
            [
                'name' => 'Yazar 1',
                'password' => Hash::make('yazar123'),
                'role' => 'yazar',
            ]
        );
        $yazar1->assignRole($yazarRole);

        $yazar2 = User::firstOrCreate(
            ['email' => 'yazar2@gundemtr.com'],
            [
                'name' => 'Yazar 2',
                'password' => Hash::make('yazar123'),
                'role' => 'yazar',
            ]
        );
        $yazar2->assignRole($yazarRole);
    }
}
