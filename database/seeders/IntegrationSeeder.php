<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Integration::create([
            'name' => 'Nexus Analytics Hub',
            'base_api_url' => 'https://api.nexus.com',
            'documentation_url' => 'https://docs.nexus.com',
            'description_en' => 'Nexus Analytics Hub Integration',
            'description_ar' => 'دمج Nexus Analytics Hub',
            'publish' => true,
        ]);
    }
}
