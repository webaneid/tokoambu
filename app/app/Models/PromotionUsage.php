<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionUsage extends Model
{
    protected $fillable = [
        'promotion_id',
        'order_id',
        'user_id',
        'coupon_code',
        'discount_amount',
        'applied_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
