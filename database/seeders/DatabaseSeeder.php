<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ServicesSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(DashboardDemoSeeder::class);
    }
}
