<?php

namespace App\Domain\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockTransferred
{
    use Dispatchable, SerializesModels;

    public int $productId;
    public int $fromLocationId;
    public int $toLocationId;
    public float $qty;
    public ?string $notes;
    public ?\DateTimeInterface $movementDate;
    public ?string $referenceType;
    public ?int $referenceId;

    public function __construct(
        int $productId,
        int $fromLocationId,
        int $toLocationId,
        float $qty,
        ?\DateTimeInterface $movementDate = null,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ) {
        $this->productId = $productId;
        $this->fromLocationId = $fromLocationId;
        $this->toLocationId = $toLocationId;
        $this->qty = $qty;
        $this->movementDate = $movementDate;
        $this->notes = $notes;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
    }
}
