<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PortSeeder::class,
            RevenueCenterSeeder::class,
            FiscalYearSeeder::class,
            MonthSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
