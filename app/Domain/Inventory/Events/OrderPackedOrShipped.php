<?php

namespace App\Domain\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPackedOrShipped
{
    use Dispatchable, SerializesModels;

    public int $orderId;
    public array $items; // each: ['product_id'=>int,'location_id'=>int,'qty'=>float,'movement_date'=>datetime|null]

    public function __construct(int $orderId, array $items)
    {
        $this->orderId = $orderId;
        $this->items = $items;
    }
}
