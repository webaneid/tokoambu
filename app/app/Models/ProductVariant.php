<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'variant_attributes',
        'cost_price',
        'selling_price',
        'weight_grams',
        'is_active',
        'variant_image_id',
    ];

    protected $casts = [
        'variant_attributes' => 'array',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Append accessors to JSON serialization.
     */
    protected $appends = [
        'total_stock',
        'display_name',
    ];

    /**
     * Get the product that owns this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant image.
     */
    public function variantImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'variant_image_id');
    }

    /**
     * Alias for variantImage (for consistency with Product model).
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->variantImage();
    }

    /**
     * Get all inventory balances for this variant.
     */
    public function inventoryBalances(): HasMany
    {
        return $this->hasMany(InventoryBalance::class);
    }

    /**
     * Get all order items for this variant.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all purchase items for this variant.
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get all stock movements for this variant.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get display name for this variant (e.g., "M / Merah").
     */
    public function getDisplayNameAttribute(): string
    {
        return implode(' / ', $this->variant_attributes);
    }

    /**
     * Get total stock across all locations for this variant.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventoryBalances()->sum('qty_on_hand');
    }

    /**
     * Scope to only active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
