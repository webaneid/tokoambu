<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin User
        $superAdmin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'super@tokoambu.com',
            'password' => bcrypt('super'),
            'email_verified_at' => now(),
        ]);

        // Assign super admin role (matching the seeder role name)
        $superAdmin->assignRole('Super Admin');
    }
}
