<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreorderPeriod extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'target_quantity',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the product that this period belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all orders in this period
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'preorder_period_id');
    }

    /**
     * Check if period is currently active (within date range)
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if period has ended
     */
    public function hasEnded(): bool
    {
        return now()->isAfter($this->end_date);
    }

    /**
     * Get total orders count for this period
     */
    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()
            ->where('type', 'preorder')
            ->whereIn('status', ['waiting_dp', 'dp_paid', 'product_ready', 'waiting_payment', 'paid', 'packed', 'shipped', 'done'])
            ->count();
    }

    /**
     * Get total quantity ordered in this period
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->orders()
            ->where('type', 'preorder')
            ->whereIn('status', ['waiting_dp', 'dp_paid', 'product_ready', 'waiting_payment', 'paid', 'packed', 'shipped', 'done'])
            ->with('items')
            ->get()
            ->sum(function ($order) {
                return $order->items->where('product_id', $this->product_id)->sum('quantity');
            });
    }

    /**
     * Close this period (no new orders allowed)
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Archive this period
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Reopen this period
     */
    public function reopen(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Aktif',
            'closed' => 'Ditutup',
            'archived' => 'Diarsipkan',
            default => $this->status,
        };
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'closed' => 'orange',
            'archived' => 'gray',
            default => 'gray',
        };
    }
}
