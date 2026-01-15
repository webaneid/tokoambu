<?php

namespace App\Jobs;

use App\Models\InventoryAnalytics;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeDeadStockStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $slowDays = (int) (Setting::get('dead_stock_slow_days', 60));
        $deadDays = (int) (Setting::get('dead_stock_dead_days', 120));
        $today = Carbon::today();

        // We only care about products that have balances > 0
        $balances = InventoryBalance::with('location')->where('qty_on_hand', '>', 0)->get();

        foreach ($balances as $balance) {
            $productId = $balance->product_id;
            $locationId = $balance->location_id;

            $lastOut = StockMovement::where('product_id', $productId)
                ->where(function ($q) {
                    $q->where('movement_type', 'ship')
                      ->orWhere(function ($q2) {
                          $q2->where('movement_type', 'adjust')->whereColumn('from_location_id', '!=', 'to_location_id');
                      });
                })
                ->where(function ($q) use ($locationId) {
                    $q->where('from_location_id', $locationId)->orWhere('to_location_id', $locationId);
                })
                ->orderByDesc('movement_date')
                ->value('movement_date');

            $lastIn = StockMovement::where('product_id', $productId)
                ->where('movement_type', 'receive')
                ->where('to_location_id', $locationId)
                ->orderByDesc('movement_date')
                ->value('movement_date');

            $lastOutDate = $lastOut ? Carbon::parse($lastOut)->toDateString() : null;
            $lastInDate = $lastIn ? Carbon::parse($lastIn)->toDateString() : null;

            $daysSinceOut = $lastOut ? Carbon::parse($lastOut)->diffInDays($today) : 9999;
            $status = 'active';
            if ($daysSinceOut >= $deadDays) {
                $status = 'dead_stock';
            } elseif ($daysSinceOut >= $slowDays) {
                $status = 'slow_moving';
            }

            InventoryAnalytics::updateOrCreate(
                [
                    'product_id' => $productId,
                    'location_id' => $locationId,
                ],
                [
                    'last_in_date' => $lastInDate,
                    'last_out_date' => $lastOutDate,
                    'dead_stock_days' => $daysSinceOut,
                    'status' => $status,
                ]
            );
        }
    }
}
