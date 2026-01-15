<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\StockOpnameConfirmed;
use App\Domain\Inventory\Services\InventoryService;

class CreateOpnameAdjustments
{
    public function __construct(private InventoryService $inventory) {}

    public function handle(StockOpnameConfirmed $event): void
    {
        foreach ($event->items as $item) {
            $this->inventory->opnameAdjustment(
                $item['product_id'],
                $event->locationId,
                $item['system_qty'],
                $item['physical_qty'],
                [
                    'reference_type' => 'stock_opname',
                    'reference_id' => $event->locationId,
                    'movement_date' => $item['movement_date'] ?? now(),
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }
    }
}
