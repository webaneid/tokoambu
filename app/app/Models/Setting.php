<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        if ($setting->is_encrypted) {
            return decrypt($setting->value);
        }

        return $setting->value;
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Set a setting with encryption option
     */
    public static function setEncrypted($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => encrypt($value), 'is_encrypted' => true]
        );
    }

    /**
     * Check if DP is required for preorder (default true)
     */
    public static function isPreorderDpRequired(): bool
    {
        return (bool) self::get('preorder_dp_required', true);
    }

    /**
     * Get preorder DP percentage (default 30%)
     */
    public static function getPreorderDpPercentage(): int
    {
        return (int) self::get('preorder_dp_percentage', 30);
    }

    /**
     * Get preorder DP deadline in days (default 7 days)
     */
    public static function getPreorderDpDeadlineDays(): int
    {
        return (int) self::get('preorder_dp_deadline_days', 7);
    }

    /**
     * Get preorder final payment deadline in days (default 7 days)
     */
    public static function getPreorderFinalDeadlineDays(): int
    {
        return (int) self::get('preorder_final_deadline_days', 7);
    }

    /**
     * Get WhatsApp template for preorder notifications
     */
    public static function getPreorderWaTemplate(string $type): string
    {
        $key = "preorder_wa_{$type}";
        return self::get($key, '');
    }

    /**
     * Parse WhatsApp template with variables
     */
    public static function parseWaTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        return $template;
    }
}
