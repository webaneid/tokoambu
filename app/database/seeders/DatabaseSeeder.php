<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Create Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'super@tokoambu.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('super'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // Create Operator
        $operator = User::updateOrCreate(
            ['email' => 'operator@tokoambu.com'],
            [
                'name' => 'Operator Toko',
                'password' => Hash::make('operator123'),
                'email_verified_at' => now(),
            ]
        );
        $operator->assignRole('Operator');

        // Create Finance
        $finance = User::updateOrCreate(
            ['email' => 'finance@tokoambu.com'],
            [
                'name' => 'Finance Manager',
                'password' => Hash::make('finance123'),
                'email_verified_at' => now(),
            ]
        );
        $finance->assignRole('Finance');
    }
}
