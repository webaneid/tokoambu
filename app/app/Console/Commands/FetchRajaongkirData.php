<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\Province;
use App\Models\City;
use App\Models\District;

class FetchRajaongkirData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rajaongkir:fetch {--save-json : Save to JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch location data from RajaOngkir API and save to database/JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config('services.rajaongkir.key') ?? env('RAJAONGKIR_API_KEY');

        if (!$apiKey) {
            $this->error('❌ RAJAONGKIR_API_KEY not found in .env');
            return 1;
        }

        $this->info('🚀 Fetching provinces...');
        $baseUrl = 'https://rajaongkir.komerce.id/api/v1';

        try {
            // Fetch provinces
            $this->info("⏳ Fetching from $baseUrl...");
            sleep(1);
            
            $response = Http::withHeaders(['Key' => $apiKey])
                ->timeout(30)
                ->get("$baseUrl/destination/province");

            if (!$response->successful()) {
                $this->error("❌ Failed to fetch provinces: {$response->status()}");
                $this->error("Response: " . json_encode($response->json()));
                return 1;
            }

            $provinces = $response->json()['data'] ?? [];
            $this->info("✅ Found " . count($provinces) . " provinces");

            $allData = [
                'provinces' => $provinces,
                'cities' => [],
                'districts' => [],
                'fetched_at' => now()->toIso8601String(),
            ];

            // Fetch cities and districts
            $totalCities = 0;
            $totalDistricts = 0;
            $provinceCount = count($provinces);

            foreach ($provinces as $index => $province) {
                $this->line("  [" . ($index + 1) . "/$provinceCount] Fetching cities for {$province['name']}...");
                sleep(2); // Rate limit: 2 seconds between requests

                $cityResponse = Http::withHeaders(['Key' => $apiKey])
                    ->timeout(30)
                    ->get("$baseUrl/destination/city/{$province['id']}");

                if (!$cityResponse->successful()) {
                    $this->warn("  ⚠️  Failed to fetch cities for {$province['id']}");
                    continue;
                }

                $cities = $cityResponse->json()['data'] ?? [];
                $totalCities += count($cities);

                foreach ($cities as $city) {
                    $city['province_id'] = $province['id'];
                    $allData['cities'][] = $city;

                    // Fetch districts for this city
                    sleep(1); // Rate limit: 1 second between district requests
                    
                    $districtResponse = Http::withHeaders(['Key' => $apiKey])
                        ->timeout(30)
                        ->get("$baseUrl/destination/district/{$city['id']}");

                    if ($districtResponse->successful()) {
                        $districts = $districtResponse->json()['data'] ?? [];
                        $totalDistricts += count($districts);

                        foreach ($districts as $district) {
                            $district['city_id'] = $city['id'];
                            $allData['districts'][] = $district;
                        }
                    }
                }
            }

            $this->info("✅ Found $totalCities cities and $totalDistricts districts");

            // Save to database
            $this->info('💾 Saving to database...');
            $this->saveToDatabase($allData);

            // Save to JSON if requested
            if ($this->option('save-json')) {
                $this->info('📝 Saving to JSON file...');
                $this->saveToJson($allData);
            }

            $this->info('✅ Done! Data fetched successfully');
            $this->line("   Provinces: " . count($allData['provinces']));
            $this->line("   Cities: " . count($allData['cities']));
            $this->line("   Districts: " . count($allData['districts']));

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    protected function saveToDatabase($data)
    {
        // Clear existing data
        District::truncate();
        City::truncate();
        Province::truncate();

        // Insert provinces
        foreach ($data['provinces'] as $province) {
            Province::create([
                'id' => $province['id'],
                'name' => $province['name'],
            ]);
        }

        // Insert cities
        foreach ($data['cities'] as $city) {
            City::create([
                'id' => $city['id'],
                'province_id' => $city['province_id'],
                'name' => $city['name'],
                'type' => $city['type'] ?? null,
            ]);
        }

        // Insert districts
        foreach ($data['districts'] as $district) {
            District::create([
                'id' => $district['id'],
                'city_id' => $district['city_id'],
                'name' => $district['name'],
                'postal_code' => $district['zip_code'] ?? null,
            ]);
        }
    }

    protected function saveToJson($data)
    {
        $path = storage_path('rajaongkir/locations.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("✅ JSON saved to: $path");
    }
}
