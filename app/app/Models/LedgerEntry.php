<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'type',
        'category_id',
        'description',
        'amount',
        'payment_method',
        'payee_bank_account_id',
        'payer_bank_account_id',
        'payment_media_id',
        'reference_id',
        'reference_type',
        'notes',
        'entry_date',
        'source_type',
        'source_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payeeBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'payee_bank_account_id');
    }

    public function payerBankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'payer_bank_account_id');
    }

    public function paymentMedia()
    {
        return $this->belongsTo(Media::class, 'payment_media_id');
    }

    public function source()
    {
        return $this->morphTo();
    }
}
