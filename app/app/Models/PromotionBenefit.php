<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionBenefit extends Model
{
    protected $fillable = [
        'promotion_id',
        'benefit_type',
        'value',
        'max_discount',
        'apply_scope',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
