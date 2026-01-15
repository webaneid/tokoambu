<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\PurchaseReceived;
use App\Domain\Inventory\Services\InventoryService;

class CreateReceiveMovementsAndIncreaseBalances
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(PurchaseReceived $event): void
    {
        foreach ($event->items as $item) {
            $variantId = $item['product_variant_id'] ?? null;

            $movement = $this->inventory->receive(
                $item['product_id'],
                $item['location_id'],
                $item['qty'],
                [
                    'product_variant_id' => $variantId,
                    'reference_type' => 'purchase',
                    'reference_id' => $event->purchaseId,
                    'movement_date' => $item['movement_date'] ?? now(),
                ]
            );

            if ($movement->wasRecentlyCreated) {
                $this->inventory->allocatePreorderBacklog(
                    $item['product_id'],
                    $item['qty'],
                    $variantId
                );
            }
        }
    }
}
