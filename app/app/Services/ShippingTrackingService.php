<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ShippingTrackingService
{
    public function track(string $courier, string $awb): array
    {
        $apiKey = Setting::get('rajaongkir_key') ?: env('RAJAONGKIR_API_KEY');
        if (!$apiKey) {
            return [
                'ok' => false,
                'status' => 422,
                'message' => 'RajaOngkir API key belum diatur.',
            ];
        }

        $baseUrl = config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1');
        $endpoint = config('rajaongkir.tracking_endpoint', '/track/waybill');
        $endpoint = str_starts_with($endpoint, '/') ? $endpoint : '/' . $endpoint;

        $query = http_build_query([
            'awb' => $awb,
            'courier' => $courier,
        ]);

        try {
            $response = Http::withHeaders([
                'key' => $apiKey,
                'accept' => 'application/json',
            ])
                ->timeout(30)
                ->post($baseUrl . $endpoint . '?' . $query);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'status' => $response->status(),
                    'message' => 'Gagal mengambil status pengiriman.',
                    'response' => $response->json(),
                    'body' => $response->body(),
                ];
            }

            $payload = $response->json();
            $data = $payload['data'] ?? [];
            $status = $data['delivery_status']['status']
                ?? $data['summary']['status']
                ?? null;
            $delivered = (bool)($data['delivered'] ?? false);
            if (!$delivered && $status) {
                $normalized = strtoupper(trim((string)$status));
                $delivered = str_contains($normalized, 'DELIVERED');
            }

            $deliveredInfo = $this->extractDeliveredInfo($data);

            return [
                'ok' => true,
                'payload' => $payload,
                'delivered' => $delivered,
                'tracking_status' => $status,
                'delivered_at' => $deliveredInfo['delivered_at'],
                'received_by' => $deliveredInfo['received_by'],
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 500,
                'message' => 'Gagal menghubungi layanan pelacakan.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function extractDeliveredInfo(array $data): array
    {
        $deliveredAt = null;
        $receivedBy = null;

        $deliveryStatus = $data['delivery_status'] ?? [];
        $receivedBy = $deliveryStatus['pod_receiver']
            ?? $deliveryStatus['received_by']
            ?? null;
        if ($receivedBy && preg_match('/(?:DITERIMA OLEH|RECEIVED BY)\\s+(.+)/i', (string) $receivedBy, $matches)) {
            $receivedBy = trim($matches[1]);
        }

        $podDate = $deliveryStatus['pod_date'] ?? null;
        $podTime = $deliveryStatus['pod_time'] ?? null;
        $podDatetime = $deliveryStatus['pod_datetime'] ?? null;
        $deliveredAt = $this->parseDateTime($podDate, $podTime, $podDatetime);

        $summary = $data['summary'] ?? [];
        if (!$deliveredAt) {
            $deliveredAt = $this->parseDateTime(
                $summary['delivered_date'] ?? null,
                $summary['delivered_time'] ?? null,
                $summary['delivered_at'] ?? null
            );
        }
        if (!$receivedBy) {
            $receivedBy = $summary['received_by'] ?? null;
        }

        $events = $data['manifest']
            ?? $data['history']
            ?? $data['tracking']
            ?? $data['details']
            ?? [];

        if (is_array($events) && $events) {
            foreach (array_reverse($events) as $event) {
                if (!is_array($event)) {
                    continue;
                }
                $description = $event['description']
                    ?? $event['message']
                    ?? $event['remarks']
                    ?? $event['detail']
                    ?? '';
                $normalized = strtoupper((string) $description);
                if ($normalized === '') {
                    continue;
                }
                if (!str_contains($normalized, 'DELIVERED') && !str_contains($normalized, 'DITERIMA')) {
                    continue;
                }

                if (!$deliveredAt) {
                    $deliveredAt = $this->parseDateTime(
                        $event['date'] ?? null,
                        $event['time'] ?? null,
                        $event['datetime'] ?? $event['timestamp'] ?? null
                    );
                }

                if (!$receivedBy) {
                    if (preg_match('/(?:DITERIMA OLEH|RECEIVED BY)\\s+(.+)/i', (string) $description, $matches)) {
                        $receivedBy = trim($matches[1]);
                    }
                }
                break;
            }
        }

        return [
            'delivered_at' => $deliveredAt,
            'received_by' => $receivedBy,
        ];
    }

    private function parseDateTime(?string $date, ?string $time, ?string $datetime): ?Carbon
    {
        if ($datetime) {
            try {
                return Carbon::parse($datetime);
            } catch (\Throwable $e) {
                // ignore parse error
            }
        }

        if ($date && $time) {
            try {
                return Carbon::parse(trim($date . ' ' . $time));
            } catch (\Throwable $e) {
                // ignore parse error
            }
        }

        if ($date) {
            try {
                return Carbon::parse($date);
            } catch (\Throwable $e) {
                // ignore parse error
            }
        }

        return null;
    }
}
