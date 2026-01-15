<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplierPrice extends Model
{
    protected $fillable = [
        'product_id',
        'supplier_id',
        'last_cost',
        'avg_cost',
        'last_purchase_at',
    ];

    protected $casts = [
        'last_purchase_at' => 'datetime',
        'last_cost' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
