<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'filename',
        'path',
        'mime',
        'size',
        'metadata',
        'uploaded_by',
        'product_id',
        'gallery_order',
        'purchase_id',
        'payment_id',
        'purchase_payment_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function purchasePayment()
    {
        return $this->belongsTo(PurchasePayment::class);
    }

    public function scopePaymentProof($query)
    {
        return $query->where('type', 'payment_proof');
    }

    public function scopeProductPhoto($query)
    {
        return $query->where('type', 'product_photo');
    }

    public function scopeBannerImage($query)
    {
        return $query->where('type', 'banner_image');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
