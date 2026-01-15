<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\ProductSupplierPrice;
use App\Jobs\SyncShipmentTrackingJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sync:product-costs', function () {
    $this->info('Sinkronisasi cost_price produk dari harga terakhir supplier...');
    $count = 0;
    Product::with('supplierPrices')->chunk(200, function ($products) use (&$count) {
        foreach ($products as $product) {
            $latest = $product->supplierPrices()
                ->orderByDesc('last_purchase_at')
                ->orderByDesc('updated_at')
                ->first();
            if ($latest && $latest->last_cost != $product->cost_price) {
                $product->update(['cost_price' => $latest->last_cost]);
                $count++;
            }
        }
    });
    $this->info("Selesai. Produk yang diperbarui: {$count}");
})->purpose('Sinkronisasi harga modal produk dengan harga terakhir per supplier');

Artisan::command('tracking:sync', function () {
    $this->info('Sinkronisasi status pengiriman...');
    SyncShipmentTrackingJob::dispatchSync();
    $this->info('Selesai.');
})->purpose('Sinkronisasi status pelacakan pengiriman secara manual');
