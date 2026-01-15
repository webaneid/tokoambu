<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulateDistrictPostalCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('districts', 'code')) {
            $this->command->warn('Seeder ini memakai kode Kemendagri dan tidak kompatibel dengan schema RajaOngkir.');
            return;
        }

        $filePath = '/tmp/wilayah-indonesia/sql/sub-districts/insert-data.sql';
        
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: $filePath");
            return;
        }

        $content = file_get_contents($filePath);
        $statements = array_filter(array_map('trim', explode(';', $content)));

        // Extract postal codes from sub-districts
        $districtPostalCodes = [];
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                // Parse: INSERT INTO sub_districts (code, name, district_code, postal_code) VALUES ('31.71.01.2001', 'name', '31.71.01', '12000');
                if (preg_match("/VALUES\s*\('([^']+)',\s*'[^']*',\s*'([^']+)',\s*'([^']+)'\)/", $statement, $matches)) {
                    $subDistrictCode = $matches[1];
                    $districtCode = $matches[2];
                    $postalCode = $matches[3];
                    
                    // Only keep first postal code per district
                    if (!isset($districtPostalCodes[$districtCode])) {
                        $districtPostalCodes[$districtCode] = $postalCode;
                    }
                }
            }
        }

        // Update districts with postal codes
        $updated = 0;
        foreach ($districtPostalCodes as $districtCode => $postalCode) {
            DB::table('districts')
                ->where('code', $districtCode)
                ->update(['postal_code' => $postalCode]);
            $updated++;
        }

        $this->command->info("Updated $updated districts with postal codes");
    }
}
