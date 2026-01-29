<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyItemRecurrents extends BaseModel
{
    protected $fillable = [
        'recurrent_id',
        'payment_day',
        'reference_month',
        'reference_year',
        'amount',
        'status'
    ];
}
