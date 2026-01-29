<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Auth\User;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Card extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'account_id',
        'cardholder_name',
        'last_four_digits',
        'brand',
        'color_card',
        'credit_limit',
        'closing_day',
        'due_day',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function invoiceItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            InvoiceItem::class,
            Invoice::class,
            'card_id',
            'invoice_id',
            'id',
            'id'
        );
    }
}
