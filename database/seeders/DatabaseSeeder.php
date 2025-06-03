<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            ProductTypeSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            ReferrerSeeder::class,
            CustomerSeeder::class,
            LoanApplicationSeeder::class,
        ]);
    }
}
