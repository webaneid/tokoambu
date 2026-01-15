<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $fillable = [
        'promotion_id',
        'pricing_mode',
        'bundle_price',
        'discount_value',
        'must_be_cheaper',
        'compare_basis',
        'featured_media_id',
    ];

    protected $casts = [
        'must_be_cheaper' => 'boolean',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function items()
    {
        return $this->hasMany(BundleItem::class);
    }

    public function featuredMedia()
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }
}
