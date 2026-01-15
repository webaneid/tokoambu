<?php

namespace App\Domain\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAdjusted
{
    use Dispatchable, SerializesModels;

    public int $productId;
    public int $locationId;
    public float $qtyChange; // positive add, negative reduce
    public string $reason;
    public ?string $notes;
    public ?\DateTimeInterface $movementDate;
    public ?string $referenceType;
    public ?int $referenceId;

    public function __construct(
        int $productId,
        int $locationId,
        float $qtyChange,
        string $reason,
        ?\DateTimeInterface $movementDate = null,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ) {
        $this->productId = $productId;
        $this->locationId = $locationId;
        $this->qtyChange = $qtyChange;
        $this->reason = $reason;
        $this->movementDate = $movementDate;
        $this->notes = $notes;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
    }
}
