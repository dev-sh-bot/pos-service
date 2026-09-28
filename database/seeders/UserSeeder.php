<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::updateOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'Admin',
            'description' => 'Full system access.',
            'is_system' => true,
        ]);

        $userRole = Role::updateOrCreate([
            'slug' => 'user',
        ], [
            'name' => 'User',
            'description' => 'Standard user access.',
            'is_system' => true,
        ]);

        $admin = User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'first_name' => 'Admin',
            'last_name' => 'admin',
            'password' => bcrypt('admin123'),
            'is_active' => true,
        ]);

        $user = User::updateOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'first_name' => 'Regular',
            'last_name' => 'User',
            'password' => bcrypt('user123'),
            'is_active' => true,
        ]);

        $admin->syncRoles([$adminRole->id]);
        $user->syncRoles([$userRole->id]);
    }
}
