<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_date',
        'paid_amount',
        'supplier_bank_account_id',
        'payer_bank_account_id',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function getVerifiedPaidAmountAttribute()
    {
        return $this->payments()->where('status', 'verified')->sum('amount');
    }
}
