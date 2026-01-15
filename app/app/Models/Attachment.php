<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'payment_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
