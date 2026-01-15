<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\StockAdjusted;
use App\Domain\Inventory\Services\InventoryService;

class ApplyAdjustmentAndCreateMovement
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(StockAdjusted $event): void
    {
        $this->inventory->adjust(
            $event->productId,
            $event->locationId,
            $event->qtyChange,
            $event->reason,
            [
                'notes' => $event->notes,
                'reference_type' => $event->referenceType ?? 'manual',
                'reference_id' => $event->referenceId,
                'movement_date' => $event->movementDate ?? now(),
            ]
        );
    }
}
