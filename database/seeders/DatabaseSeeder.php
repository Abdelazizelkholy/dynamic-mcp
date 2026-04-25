<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,  // 1. create permissions first
            AdminUserSeeder::class,   // 2. create admin user + assign role
            IntegrationSeeder::class, // 3. seed integrations
        ]);
    }
}
