<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'custom_fields'];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
