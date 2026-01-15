<?php

namespace App\Domain\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockOpnameConfirmed
{
    use Dispatchable, SerializesModels;

    public int $locationId;
    public array $items; // each: ['product_id'=>int, 'system_qty'=>float, 'physical_qty'=>float, 'movement_date'=>datetime|null]

    public function __construct(int $locationId, array $items)
    {
        $this->locationId = $locationId;
        $this->items = $items;
    }
}
