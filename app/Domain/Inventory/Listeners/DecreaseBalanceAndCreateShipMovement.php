<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\OrderPackedOrShipped;
use App\Domain\Inventory\Services\InventoryService;

class DecreaseBalanceAndCreateShipMovement
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(OrderPackedOrShipped $event): void
    {
        foreach ($event->items as $item) {
            $this->inventory->ship(
                $item['product_id'],
                $item['location_id'],
                $item['qty'],
                [
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'reference_type' => 'order',
                    'reference_id' => $event->orderId,
                    'movement_date' => $item['movement_date'] ?? now(),
                ]
            );
        }
    }
}
