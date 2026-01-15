<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'bundle_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the cart that owns this item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant for this cart item (if applicable).
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the bundle for this cart item (if applicable).
     */
    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * Calculate line total (quantity * price).
     */
    public function getLineTotal(): float
    {
        return $this->quantity * $this->price;
    }

    /**
     * Get formatted line total for display.
     */
    public function getLineTotalFormatted(): string
    {
        return 'Rp ' . number_format($this->getLineTotal(), 0, ',', '.');
    }

    /**
     * Get formatted unit price for display.
     */
    public function getPriceFormatted(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
