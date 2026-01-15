<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id', 'name'];

    public function cities()
    {
        return $this->hasMany(City::class, 'province_id', 'id');
    }

    public function districts()
    {
        return $this->hasManyThrough(District::class, City::class, 'province_id', 'city_id', 'id', 'id');
    }
}
