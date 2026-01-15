<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert sample provinces
        $provinces = [
            ['id' => 11, 'name' => 'DKI JAKARTA'],
            ['id' => 12, 'name' => 'BANTEN'],
            ['id' => 6, 'name' => 'JAWA BARAT'],
        ];

        foreach ($provinces as $province) {
            Province::firstOrCreate(['id' => $province['id']], $province);
        }

        // Insert sample cities for DKI JAKARTA (11)
        $jakartaCities = [
            ['id' => 1360, 'province_id' => 11, 'name' => 'JAKARTA SELATAN', 'zip_code' => '0'],
            ['id' => 1375, 'province_id' => 11, 'name' => 'JAKARTA PUSAT', 'zip_code' => '0'],
            ['id' => 1376, 'province_id' => 11, 'name' => 'JAKARTA BARAT', 'zip_code' => '0'],
            ['id' => 1377, 'province_id' => 11, 'name' => 'JAKARTA TIMUR', 'zip_code' => '0'],
            ['id' => 1378, 'province_id' => 11, 'name' => 'JAKARTA UTARA', 'zip_code' => '0'],
        ];

        foreach ($jakartaCities as $city) {
            City::firstOrCreate(['id' => $city['id']], $city);
        }

        // Insert sample cities for BANTEN (12)
        $bantenCities = [
            ['id' => 1400, 'province_id' => 12, 'name' => 'KOTA TANGERANG', 'zip_code' => '0'],
            ['id' => 1401, 'province_id' => 12, 'name' => 'KOTA SERANG', 'zip_code' => '0'],
            ['id' => 1402, 'province_id' => 12, 'name' => 'KOTA CILEGON', 'zip_code' => '0'],
        ];

        foreach ($bantenCities as $city) {
            City::firstOrCreate(['id' => $city['id']], $city);
        }

        // Insert sample districts for JAKARTA SELATAN (1360)
        $jakartaSelDistricts = [
            ['id' => 13601, 'city_id' => 1360, 'name' => 'TEBET', 'zip_code' => '12840'],
            ['id' => 13602, 'city_id' => 1360, 'name' => 'SETIA BUDI', 'zip_code' => '12980'],
            ['id' => 13603, 'city_id' => 1360, 'name' => 'JAGAKARSA', 'zip_code' => '12630'],
            ['id' => 13604, 'city_id' => 1360, 'name' => 'CILANDAK', 'zip_code' => '12430'],
        ];

        foreach ($jakartaSelDistricts as $district) {
            District::firstOrCreate(['id' => $district['id']], $district);
        }

        // Insert sample districts for JAKARTA PUSAT (1375)
        $jakartaPusatDistricts = [
            ['id' => 13751, 'city_id' => 1375, 'name' => 'MENTENG', 'zip_code' => '10310'],
            ['id' => 13752, 'city_id' => 1375, 'name' => 'TANAH ABANG', 'zip_code' => '10160'],
            ['id' => 13753, 'city_id' => 1375, 'name' => 'GAMBIR', 'zip_code' => '10110'],
        ];

        foreach ($jakartaPusatDistricts as $district) {
            District::firstOrCreate(['id' => $district['id']], $district);
        }

        // Insert sample districts for TANGERANG (1400)
        $tangerangDistricts = [
            ['id' => 14001, 'city_id' => 1400, 'name' => 'PINANG', 'zip_code' => '15144'],
            ['id' => 14002, 'city_id' => 1400, 'name' => 'CIPONDOH', 'zip_code' => '15148'],
            ['id' => 14003, 'city_id' => 1400, 'name' => 'BATUCEPER', 'zip_code' => '15121'],
        ];

        foreach ($tangerangDistricts as $district) {
            District::firstOrCreate(['id' => $district['id']], $district);
        }

        $this->command->info('Location data seeded successfully!');
    }
}
