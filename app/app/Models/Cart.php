<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'session_id',
    ];

    /**
     * Get the customer that owns this cart.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the items in this cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate total quantity of items in cart.
     */
    public function getTotalQuantity(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculate total price (subtotal) of cart.
     */
    public function getSubtotal(): float
    {
        return $this->items()
            ->selectRaw('SUM(quantity * price) as total')
            ->first()
            ->total ?? 0;
    }

    /**
     * Get formatted subtotal for display.
     */
    public function getSubtotalFormatted(): string
    {
        return 'Rp ' . number_format($this->getSubtotal(), 0, ',', '.');
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    /**
     * Clear all items from cart.
     */
    public function clear(): void
    {
        $this->items()->delete();
    }
}
