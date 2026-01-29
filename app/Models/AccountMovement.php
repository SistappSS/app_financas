<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AccountMovement extends Model
{
    use HasFactory;

    protected $table = 'account_movements';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'account_id',
        'occurred_at',
        'amount',
        'type',
        'description',
        'transaction_id',
        'payment_transaction_id',
        'invoice_id',
        'saving_id',
        'transfer_group_id',
        'balance_after',
    ];

    protected $casts = [
        'occurred_at'   => 'datetime',
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (AccountMovement $movement) {
            if (empty($movement->id)) {
                $movement->id = (string) Str::uuid();
            }
        });
    }
}

