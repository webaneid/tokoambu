<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'description',
        'status',
        'priority',
        'stackable',
        'start_at',
        'end_at',
        'rules',
        'created_by',
    ];

    protected $casts = [
        'stackable' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'rules' => 'array',
    ];

    public function targets()
    {
        return $this->hasMany(PromotionTarget::class);
    }

    public function benefits()
    {
        return $this->hasMany(PromotionBenefit::class);
    }

    public function coupon()
    {
        return $this->hasOne(Coupon::class);
    }

    public function bundle()
    {
        return $this->hasOne(Bundle::class);
    }

    public function usages()
    {
        return $this->hasMany(PromotionUsage::class);
    }
}
