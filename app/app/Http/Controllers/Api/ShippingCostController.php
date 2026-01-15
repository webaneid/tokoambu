<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingCostController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'origin_district_id' => 'required|integer',
            'destination_district_id' => 'required|integer',
            'weight_grams' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        $apiKey = Setting::get('rajaongkir_key') ?: env('RAJAONGKIR_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'message' => 'RajaOngkir API key belum diatur.',
            ], 422);
        }

        $baseUrl = config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1');
        $endpoint = config('rajaongkir.cost_endpoint', '/calculate/district/domestic-cost');
        $endpoint = str_starts_with($endpoint, '/') ? $endpoint : '/' . $endpoint;

        $payload = [
            'origin' => $validated['origin_district_id'],
            'destination' => $validated['destination_district_id'],
            'weight' => $validated['weight_grams'],
            'courier' => $validated['courier'],
        ];

        try {
            $response = Http::withHeaders([
                'key' => $apiKey,
                'accept' => 'application/json',
            ])
                ->asForm()
                ->timeout(30)
                ->post($baseUrl . $endpoint, $payload);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal menghitung ongkir.',
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'body' => $response->body(),
                ], $response->status());
            }

            return response()->json($response->json());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menghubungi layanan ongkir.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
