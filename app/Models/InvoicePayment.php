<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoicePayment extends BaseModel
{
    use BelongsToUser;

    protected $fillable = ['invoice_id','user_id','amount','paid_at','method','reference'];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function invoice(){ return $this->belongsTo(Invoice::class); }
}
