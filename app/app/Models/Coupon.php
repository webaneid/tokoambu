<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'promotion_id',
        'code',
        'per_user_limit',
        'global_limit',
        'min_order_amount',
        'first_purchase_only',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'first_purchase_only' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
