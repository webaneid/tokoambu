<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\StockTransferred;
use App\Domain\Inventory\Services\InventoryService;

class MoveBalancesAndCreateTransferMovement
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(StockTransferred $event): void
    {
        $this->inventory->transfer(
            $event->productId,
            $event->fromLocationId,
            $event->toLocationId,
            $event->qty,
            [
                'reference_type' => $event->referenceType ?? 'manual',
                'reference_id' => $event->referenceId,
                'notes' => $event->notes,
                'movement_date' => $event->movementDate ?? now(),
            ]
        );
    }
}
