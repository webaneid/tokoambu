<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'requested_by',
        'source_media_id',
        'result_media_id',
        'prompt',
        'request_payload',
        'response_meta',
        'status',
        'error_message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_meta' => 'array',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function sourceMedia()
    {
        return $this->belongsTo(Media::class, 'source_media_id');
    }

    public function resultMedia()
    {
        return $this->belongsTo(Media::class, 'result_media_id');
    }
}
