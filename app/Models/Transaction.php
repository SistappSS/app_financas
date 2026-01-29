<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'card_id',
        'saving_id',
        'account_id',
        'transaction_category_id',
        'title',
        'amount',
        'date',
        'type',
        'type_card',
        'recurrence_type',
        'custom_occurrences',
        'create_date'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }
}
