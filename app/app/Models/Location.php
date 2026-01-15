<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'warehouse_id',
        'code',
        'location_attributes',
        'zone',
        'rack',
        'bin',
        'description',
        'is_active',
    ];

    protected $casts = [
        'location_attributes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Append accessors to JSON serialization.
     */
    protected $appends = [
        'display_code',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function balances()
    {
        return $this->hasMany(InventoryBalance::class);
    }

    /**
     * Get display code from location_attributes or fallback to code.
     */
    public function getDisplayCodeAttribute(): string
    {
        if ($this->location_attributes && is_array($this->location_attributes) && count($this->location_attributes) > 0) {
            return implode('-', array_values($this->location_attributes));
        }

        return $this->code ?? '-';
    }
}
