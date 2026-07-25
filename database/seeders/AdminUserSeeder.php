<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@app.com',
            'password' => Hash::make('password'),
            'phone' => '01000000000',
            'api_key' => 'dev_admin_api_key_9f3c2b7a1e5d4f6081a9c3b2e7d5f4a1',
        ]);

        $user->assignRole($role);
    }
}
