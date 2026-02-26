<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends BaseModel
{
    protected $fillable = [
        'user_id',
        'plan_name',
        'amount',
        'status',
        'trial_ends_at',
        'current_period_ends_at',
        'canceled_at',
        'asaas_customer_id',
        'asaas_subscription_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
