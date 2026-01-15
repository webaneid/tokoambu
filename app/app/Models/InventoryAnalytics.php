<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAnalytics extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'last_in_date',
        'last_out_date',
        'dead_stock_days',
        'status',
    ];

    protected $casts = [
        'last_in_date' => 'date',
        'last_out_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
