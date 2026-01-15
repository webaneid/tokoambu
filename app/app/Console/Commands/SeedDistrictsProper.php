<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\District;

class SeedDistrictsProper extends Command
{
    protected $signature = 'seed:districts-proper';
    protected $description = 'Seed proper Indonesia district names (kecamatan)';

    public function handle()
    {
        $this->info('🚀 Seeding proper district names...');

        // Clear existing districts
        District::truncate();

        $districtData = [
            // Jakarta Pusat (city_id 1064)
            1064 => [
                ['name' => 'Menteng', 'zip_code' => '12190'],
                ['name' => 'Tebet', 'zip_code' => '12820'],
                ['name' => 'Senen', 'zip_code' => '10410'],
                ['name' => 'Cempaka Putih', 'zip_code' => '10520'],
            ],
            // Jakarta Utara (city_id 1065)
            1065 => [
                ['name' => 'Penjaringan', 'zip_code' => '14110'],
                ['name' => 'Tanjung Priok', 'zip_code' => '14320'],
                ['name' => 'Kali Adem', 'zip_code' => '14140'],
                ['name' => 'Kelapa Gading', 'zip_code' => '14240'],
            ],
            // Jakarta Barat (city_id 1066)
            1066 => [
                ['name' => 'Kebon Jeruk', 'zip_code' => '11210'],
                ['name' => 'Grogol Petamburan', 'zip_code' => '11450'],
                ['name' => 'Cengkareng', 'zip_code' => '11730'],
                ['name' => 'Kalideres', 'zip_code' => '11820'],
            ],
            // Jakarta Selatan (city_id 1067)
            1067 => [
                ['name' => 'Kemang', 'zip_code' => '12730'],
                ['name' => 'Cilandak', 'zip_code' => '12630'],
                ['name' => 'Tebet', 'zip_code' => '12820'],
                ['name' => 'Mampang Prapatan', 'zip_code' => '12790'],
            ],
            // Jakarta Timur (city_id 1068)
            1068 => [
                ['name' => 'Cakung', 'zip_code' => '13910'],
                ['name' => 'Duren Sawit', 'zip_code' => '13440'],
                ['name' => 'Jatinegara', 'zip_code' => '13310'],
                ['name' => 'Pulogadung', 'zip_code' => '13920'],
            ],
        ];

        $districtId = 100000;

        foreach ($districtData as $cityId => $districts) {
            foreach ($districts as $district) {
                District::create([
                    'id' => $districtId++,
                    'city_id' => $cityId,
                    'name' => $district['name'],
                    'zip_code' => $district['zip_code'],
                ]);
            }
        }

        $this->info('✅ Done! Seeded ' . District::count() . ' districts with proper names');
    }
}
