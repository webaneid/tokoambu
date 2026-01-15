<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\ShippingTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncShipmentTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ShippingTrackingService $trackingService): void
    {
        Shipment::query()
            ->whereNotNull('tracking_number')
            ->whereNotNull('courier')
            ->where(function ($query) {
                $query->whereIn('status', ['packed', 'shipped', 'pending'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'delivered')
                            ->where(function ($missingQuery) {
                                $missingQuery->whereNull('delivered_at')
                                    ->orWhereNull('received_by');
                            });
                    });
            })
            ->orderBy('id')
            ->chunkById(50, function ($shipments) use ($trackingService) {
                foreach ($shipments as $shipment) {
                    $result = $trackingService->track($shipment->courier, $shipment->tracking_number);
                    if (!$result['ok']) {
                        continue;
                    }

                    $shipment->tracking_payload = $result['payload'] ?? null;
                    $shipment->tracking_status = $result['tracking_status'] ?? null;
                    $shipment->tracked_at = now();
                    if (!empty($result['delivered_at'])) {
                        $shipment->delivered_at = $result['delivered_at'];
                    }
                    if (!empty($result['received_by'])) {
                        $shipment->received_by = $result['received_by'];
                    }

                    if (!empty($result['delivered'])) {
                        $shipment->status = 'delivered';
                        if ($shipment->order && $shipment->order->status !== 'done' && $shipment->order->status !== 'cancelled') {
                            $shipment->order->update(['status' => 'done']);
                        }
                    }

                    $shipment->save();
                }
            });
    }
}
