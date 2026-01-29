<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recurrent extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'user_id','transaction_id','payment_day','amount',
        'start_date','interval_unit','interval_value',
        'include_sat','include_sun','next_run_date','active', 'alternate_cards'
    ];

    protected $casts = ['alternate_cards' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
