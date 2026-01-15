<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'location_id',
        'qty_on_hand',
        'qty_reserved',
        'reserved_qty', // New column for preorder reserved stock
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:2',
        'qty_reserved' => 'decimal:2',
        'reserved_qty' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function getQtyAvailableAttribute()
    {
        // Available = On Hand - Reserved (existing + preorder)
        return $this->qty_on_hand - $this->qty_reserved - $this->reserved_qty;
    }

    /**
     * Get total reserved stock (existing + preorder)
     */
    public function getTotalReservedAttribute()
    {
        return $this->qty_reserved + $this->reserved_qty;
    }
}
