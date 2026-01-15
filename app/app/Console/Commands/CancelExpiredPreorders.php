<?php

namespace App\Console\Commands;

use App\Domain\Inventory\Services\InventoryService;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredPreorders extends Command
{
    protected $signature = 'preorder:cancel-expired';

    protected $description = 'Cancel expired preorder orders and unreserve their stock';

    public function handle()
    {
        $this->info('Checking for expired preorder orders...');

        $inventoryService = new InventoryService();
        $cancelledCount = 0;

        // Cancel orders with expired DP payment deadline
        $expiredDp = Order::where('type', 'preorder')
            ->where('status', 'waiting_dp')
            ->whereNotNull('dp_payment_deadline')
            ->where('dp_payment_deadline', '<', now())
            ->get();

        foreach ($expiredDp as $order) {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'cancelled']);
                $this->line("Cancelled order {$order->order_number} - DP payment deadline expired");
            });
            $cancelledCount++;
        }

        // Cancel orders with expired final payment deadline and unreserve stock
        $expiredFinal = Order::where('type', 'preorder')
            ->whereIn('status', ['product_ready', 'waiting_payment'])
            ->whereNotNull('final_payment_deadline')
            ->where('final_payment_deadline', '<', now())
            ->with('items.product')
            ->get();

        foreach ($expiredFinal as $order) {
            DB::transaction(function () use ($order, $inventoryService) {
                // Unreserve stock for each item
                foreach ($order->items as $item) {
                    try {
                        // Assume default location ID = 1 (adjust if you have different logic)
                        $defaultLocationId = 1;

                        $inventoryService->unreserveStock(
                            $item->product_id,
                            $defaultLocationId,
                            $item->quantity,
                            [
                                'product_variant_id' => $item->product_variant_id,
                                'reference_type' => Order::class,
                                'reference_id' => $order->id,
                                'reason' => 'Preorder final payment expired',
                                'notes' => "Order {$order->order_number} cancelled due to final payment deadline",
                            ]
                        );
                    } catch (\Exception $e) {
                        $this->error("Failed to unreserve stock for order {$order->order_number}: {$e->getMessage()}");
                    }
                }

                $order->update(['status' => 'cancelled']);
                $this->line("Cancelled order {$order->order_number} - Final payment deadline expired, stock unreserved");
            });
            $cancelledCount++;
        }

        if ($cancelledCount > 0) {
            $this->info("✓ Cancelled {$cancelledCount} expired preorder(s)");
        } else {
            $this->info('No expired preorders found');
        }

        return 0;
    }
}
