<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionTarget extends Model
{
    protected $fillable = [
        'promotion_id',
        'target_type',
        'target_id',
        'include',
    ];

    protected $casts = [
        'include' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
