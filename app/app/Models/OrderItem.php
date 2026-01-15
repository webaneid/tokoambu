<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'product_sku',
        'price',
        'unit_price',
        'quantity',
        'subtotal',
        'is_preorder',
        'preorder_eta_date',
        'preorder_allocated_qty',
        'preorder_ready_at',
    ];

    protected $casts = [
        'price' => 'float',
        'unit_price' => 'float',
        'subtotal' => 'float',
        'is_preorder' => 'boolean',
        'preorder_eta_date' => 'date',
        'preorder_allocated_qty' => 'float',
        'preorder_ready_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that this item belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product details
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant details
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
