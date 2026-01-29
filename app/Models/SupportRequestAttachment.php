<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupportRequestAttachment extends Model
{
    use HasUuids;

    protected $table = 'support_request_attachments';

    protected $fillable = [
        'support_request_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function request()
    {
        return $this->belongsTo(SupportRequest::class, 'support_request_id');
    }
}

