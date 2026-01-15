<?php

namespace App\Services;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class LocationService
{
    protected function getApiKey(): ?string
    {
        return Setting::get('rajaongkir_key') ?: env('RAJAONGKIR_API_KEY');
    }

    protected function getBaseUrl(): string
    {
        return config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1');
    }

    protected function ensureProvincesLoaded(): void
    {
        if (Province::query()->exists()) {
            return;
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return;
        }

        $response = Http::withHeaders(['key' => $apiKey])
            ->timeout(30)
            ->get($this->getBaseUrl() . '/destination/province');

        if (!$response->successful()) {
            return;
        }

        $items = $response->json('data') ?? [];
        $rows = [];
        foreach ($items as $item) {
            if (!isset($item['id'])) {
                continue;
            }
            $rows[] = [
                'id' => (int) $item['id'],
                'name' => $item['name'] ?? '',
            ];
        }

        if ($rows) {
            Province::upsert($rows, ['id'], ['name']);
        }
    }

    protected function ensureCitiesLoaded(int $provinceId): void
    {
        if (City::where('province_id', $provinceId)->exists()) {
            return;
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return;
        }

        $response = Http::withHeaders(['key' => $apiKey])
            ->timeout(30)
            ->get($this->getBaseUrl() . '/destination/city/' . $provinceId);

        if (!$response->successful()) {
            return;
        }

        $items = $response->json('data') ?? [];
        $rows = [];
        foreach ($items as $item) {
            if (!isset($item['id'])) {
                continue;
            }
            $rows[] = [
                'id' => (int) $item['id'],
                'province_id' => $provinceId,
                'name' => $item['name'] ?? '',
                'type' => $item['type'] ?? null,
            ];
        }

        if ($rows) {
            City::upsert($rows, ['id'], ['province_id', 'name', 'type']);
        }
    }

    protected function ensureDistrictsLoaded(int $cityId): void
    {
        if (District::where('city_id', $cityId)->exists()) {
            return;
        }

        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return;
        }

        $response = Http::withHeaders(['key' => $apiKey])
            ->timeout(30)
            ->get($this->getBaseUrl() . '/destination/district/' . $cityId);

        if (!$response->successful()) {
            return;
        }

        $items = $response->json('data') ?? [];
        $rows = [];
        foreach ($items as $item) {
            if (!isset($item['id'])) {
                continue;
            }
            $rows[] = [
                'id' => (int) $item['id'],
                'city_id' => $cityId,
                'name' => $item['name'] ?? '',
                'postal_code' => $item['zip_code'] ?? null,
            ];
        }

        if ($rows) {
            District::upsert($rows, ['id'], ['city_id', 'name', 'postal_code']);
        }
    }

    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        $this->ensureProvincesLoaded();
        return Province::all(['id', 'name'])->map(fn($p) => [
            'id' => $p->id,
            'code' => $p->id,
            'name' => $p->name
        ])->toArray();
    }

    /**
     * Get cities by province code
     */
    public function getCities($provinceCode)
    {
        if ($provinceCode !== null) {
            $this->ensureCitiesLoaded((int) $provinceCode);
        }
        return City::where('province_id', $provinceCode)
            ->get(['id', 'name', 'type', 'province_id'])
            ->map(fn($c) => [
                'id' => $c->id,
                'code' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'province_id' => $c->province_id
            ])->toArray();
    }

    /**
     * Get districts by city code
     */
    public function getDistricts($cityCode)
    {
        if ($cityCode !== null) {
            $this->ensureDistrictsLoaded((int) $cityCode);
        }
        return District::where('city_id', $cityCode)
            ->get(['id', 'name', 'postal_code', 'city_id'])
            ->map(fn($d) => [
                'id' => $d->id,
                'code' => $d->id,
                'name' => $d->name,
                'city_id' => $d->city_id,
                'postal_code' => $d->postal_code
            ])->toArray();
    }

    /**
     * Autocomplete provinces by search term
     */
    public function searchProvinces($query, $limit = 6)
    {
        $this->ensureProvincesLoaded();
        $baseQuery = Province::query();

        if ($query) {
            $baseQuery->where('name', 'like', "%{$query}%");
        }

        return $baseQuery->limit($limit)
            ->get(['id', 'name'])
            ->map(fn($p) => [
                'id' => $p->id,
                'code' => $p->id,
                'name' => $p->name
            ])->toArray();
    }

    /**
     * Autocomplete cities by search term and province code
     */
    public function searchCities($query, $provinceCode = null, $limit = 6)
    {
        $baseQuery = City::query();

        if ($provinceCode) {
            $this->ensureCitiesLoaded((int) $provinceCode);
            $baseQuery->where('province_id', $provinceCode);
        }

        if ($query) {
            $baseQuery->where('name', 'like', "%{$query}%");
        }

        return $baseQuery->limit($limit)
            ->get(['id', 'name', 'type', 'province_id'])
            ->map(fn($c) => [
                'id' => $c->id,
                'code' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'province_id' => $c->province_id
            ])->toArray();
    }

    /**
     * Autocomplete districts by search term and city code
     */
    public function searchDistricts($query, $cityCode = null, $limit = 6)
    {
        $baseQuery = District::query();

        if ($cityCode) {
            $this->ensureDistrictsLoaded((int) $cityCode);
            $baseQuery->where('city_id', $cityCode);
        }

        if ($query) {
            $baseQuery->where('name', 'like', "%{$query}%");
        }

        return $baseQuery->limit($limit)
            ->get(['id', 'name', 'city_id', 'postal_code'])
            ->map(fn($d) => [
                'id' => $d->id,
                'code' => $d->id,
                'name' => $d->name,
                'city_id' => $d->city_id,
                'postal_code' => $d->postal_code
            ])->toArray();
    }
}
