<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'sender_name',
        'sender_bank',
        'shop_bank_account_id',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentProofs()
    {
        return $this->hasMany(Media::class, 'payment_id')->where('type', 'payment_proof');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function shopBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'shop_bank_account_id');
    }
}
