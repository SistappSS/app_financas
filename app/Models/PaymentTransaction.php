<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends BaseModel
{
   protected $fillable = [
        'transaction_id',
        'title',
        'amount',
        'payment_date',
        'reference_date',   // <-
        'reference_month',  // <-
        'reference_year',   // <-
        'account_id',
    ];

    protected $casts = [
        'payment_date'   => 'date',
        'reference_date' => 'date',
    ];
}
