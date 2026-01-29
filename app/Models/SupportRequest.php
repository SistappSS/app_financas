<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupportRequest extends Model
{
    use HasUuids;

    protected $table = 'support_requests';

    protected $fillable = [
        'user_id',
        'category_slug',
        'subject',
        'message',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(SupportRequestAttachment::class, 'support_request_id');
    }
}

