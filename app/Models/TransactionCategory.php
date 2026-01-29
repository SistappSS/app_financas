<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionCategory extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'monthly_limit',
        'color',
        'icon'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurrents(): HasMany
    {
        return $this->hasMany(Recurrent::class);
    }

    public function cardTransactions(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
