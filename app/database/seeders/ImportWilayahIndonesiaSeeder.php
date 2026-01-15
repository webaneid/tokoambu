<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportWilayahIndonesiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importFromFile('/tmp/wilayah-indonesia/sql/provinces/insert-data.sql', 'provinces');
        $this->importFromFile('/tmp/wilayah-indonesia/sql/cities/insert-data.sql', 'cities');
        $this->importFromFile('/tmp/wilayah-indonesia/sql/districts/insert-data.sql', 'districts');
    }

    private function importFromFile($filePath, $tableName)
    {
        if (!file_exists($filePath)) {
            $this->command->warn("File not found: $filePath");
            return;
        }

        $content = file_get_contents($filePath);
        $statements = array_filter(array_map('trim', explode(';', $content)));

        $inserted = 0;
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    DB::statement($statement);
                    $inserted++;
                } catch (\Exception $e) {
                    $this->command->error("Error importing to $tableName: " . $e->getMessage());
                }
            }
        }

        $this->command->info("Imported $inserted records into $tableName");
    }
}

