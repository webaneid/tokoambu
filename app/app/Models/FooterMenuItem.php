<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterMenuItem extends Model
{
    protected $fillable = [
        'label',
        'type',
        'page_id',
        'custom_url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the page that this menu item links to
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the URL for this menu item
     */
    public function getUrl(): string
    {
        if ($this->type === 'page' && $this->page) {
            return route('page.show', $this->page->slug);
        }

        return $this->custom_url ?? '#';
    }

    /**
     * Scope to get only active menu items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
