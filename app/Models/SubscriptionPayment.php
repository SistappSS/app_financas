<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends BaseModel
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'asaas_payment_id',
        'status',
        'amount',
        'due_date',
        'paid_at',
        'pix_qr_code',
        'pix_copy_paste',
        'invoice_url',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
