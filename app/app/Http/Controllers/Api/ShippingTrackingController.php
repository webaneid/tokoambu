<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShippingTrackingService;

class ShippingTrackingController extends Controller
{
    public function track(Request $request, ShippingTrackingService $trackingService)
    {
        $validated = $request->validate([
            'awb' => 'required_without_all:waybill,tracking_number|string',
            'waybill' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'courier' => 'required|string',
        ]);
        $awb = $validated['awb']
            ?? $validated['waybill']
            ?? $validated['tracking_number']
            ?? '';

        $result = $trackingService->track($validated['courier'], $awb);
        if (!$result['ok']) {
            return response()->json([
                'message' => $result['message'] ?? 'Gagal mengambil status pengiriman.',
                'status' => $result['status'] ?? 500,
                'response' => $result['response'] ?? null,
                'body' => $result['body'] ?? null,
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);
        }

        return response()->json($result['payload']);
    }
}
