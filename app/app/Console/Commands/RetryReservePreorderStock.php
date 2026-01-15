<?php

namespace App\Console\Commands;

use App\Domain\Inventory\Services\InventoryService;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetryReservePreorderStock extends Command
{
    protected $signature = 'preorder:retry-reserve {--order_id= : Specific order ID to retry}';

    protected $description = 'Retry reserving stock for preorder orders that are dp_paid but not reserved yet';

    public function handle()
    {
        $this->info('Checking for orders that need stock reservation...');

        $inventoryService = new InventoryService();
        $defaultLocationId = 1;

        // Get orders with dp_paid status
        $query = Order::where('type', 'preorder')
            ->whereIn('status', ['dp_paid', 'product_ready', 'waiting_payment'])
            ->with('items.product.inventoryBalances');

        if ($orderId = $this->option('order_id')) {
            $query->where('id', $orderId);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No orders found that need reservation.');
            return 0;
        }

        $this->info("Found {$orders->count()} order(s) to process.");

        $successCount = 0;
        $failCount = 0;

        foreach ($orders as $order) {
            $this->line("\nProcessing Order #{$order->order_number}...");

            try {
                DB::transaction(function () use ($order, $inventoryService, $defaultLocationId, &$successCount) {
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        $qtyNeeded = $item->quantity;

                        // Check current reserved qty
                        $currentReserved = $product->inventoryBalances->sum('reserved_qty');
                        $totalOnHand = $product->qty_on_hand;

                        $this->line("  - {$product->name}: Need {$qtyNeeded} pcs (Stock: {$totalOnHand}, Currently Reserved: {$currentReserved})");

                        try {
                            $inventoryService->reserveStock(
                                $item->product_id,
                                $defaultLocationId,
                                $qtyNeeded,
                                [
                                    'product_variant_id' => $item->product_variant_id,
                                    'reference_type' => Order::class,
                                    'reference_id' => $order->id,
                                    'reason' => 'Preorder DP paid (retry)',
                                    'notes' => "Order {$order->order_number} - Manual retry reserve stock",
                                ]
                            );

                            $this->info("    ✓ Reserved {$qtyNeeded} pcs successfully");
                        } catch (\Exception $e) {
                            $this->error("    ✗ Failed to reserve: {$e->getMessage()}");
                            throw $e;
                        }
                    }

                    $successCount++;
                });
            } catch (\Exception $e) {
                $this->error("✗ Failed to process order #{$order->order_number}: {$e->getMessage()}");
                $failCount++;
            }
        }

        $this->newLine();
        $this->info("✓ Successfully reserved: {$successCount} order(s)");
        if ($failCount > 0) {
            $this->error("✗ Failed: {$failCount} order(s)");
        }

        return $successCount > 0 ? 0 : 1;
    }
}
