<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\City;
use Illuminate\Support\Facades\File;

class ExportLocationsToJson extends Command
{
    protected $signature = 'export:locations-json';
    protected $description = 'Export all provinces and cities to JSON file for form autocomplete';

    public function handle()
    {
        $this->info('🚀 Exporting locations to JSON...');

        $provinces = Province::with('cities')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
            ])
            ->toArray();

        $cities = City::all()
            ->map(fn($c) => [
                'id' => $c->id,
                'province_id' => $c->province_id,
                'name' => $c->name,
                'zip_code' => $c->zip_code,
            ])
            ->toArray();

        $data = [
            'provinces' => $provinces,
            'cities' => $cities,
            'updated_at' => now()->toIso8601String(),
        ];

        $path = storage_path('locations/locations.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("✅ Exported " . count($provinces) . " provinces and " . count($cities) . " cities");
        $this->info("📁 File saved to: $path");
    }
}
