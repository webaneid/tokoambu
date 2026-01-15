<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\City;
use App\Models\District;

class SeedAllDistricts extends Command
{
    protected $signature = 'seed:all-districts';
    protected $description = 'Auto-seed 3 districts for every city with proper names';

    public function handle()
    {
        $this->info('🚀 Auto-seeding districts for all cities...');

        // Clear existing
        District::truncate();

        $districtId = 100000;
        $count = 0;

        // Get all cities
        $cities = City::all();

        foreach ($cities as $city) {
            // Create 3 districts per city
            for ($i = 1; $i <= 3; $i++) {
                $districtName = $city->name . ' Kecamatan ' . $i;
                
                District::create([
                    'id' => $districtId++,
                    'city_id' => $city->id,
                    'name' => $districtName,
                    'zip_code' => $city->zip_code,
                ]);
                $count++;
            }
        }

        $this->info("✅ Created $count districts for " . $cities->count() . " cities");
    }
}
