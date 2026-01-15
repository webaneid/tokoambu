<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id', 'city_id', 'name', 'postal_code'];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
