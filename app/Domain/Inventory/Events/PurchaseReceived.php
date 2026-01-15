<?php

namespace App\Domain\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseReceived
{
    use Dispatchable, SerializesModels;

    public int $purchaseId;
    public array $items; // each: ['product_id' => int, 'location_id' => int, 'qty' => float, 'movement_date' => datetime|null]

    public function __construct(int $purchaseId, array $items)
    {
        $this->purchaseId = $purchaseId;
        $this->items = $items;
    }
}
