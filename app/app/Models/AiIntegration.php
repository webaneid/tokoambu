<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'api_key',
        'model',
        'is_enabled',
        'default_bg_color',
        'use_solid_background',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'use_solid_background' => 'boolean',
        'metadata' => 'array',
    ];

    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public static function active(string $provider = 'gemini'): ?self
    {
        return static::forProvider($provider)->enabled()->first();
    }
}
