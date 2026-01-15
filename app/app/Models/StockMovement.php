<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'movement_date',
        'product_id',
        'product_variant_id',
        'from_location_id',
        'to_location_id',
        'qty',
        'movement_type',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
