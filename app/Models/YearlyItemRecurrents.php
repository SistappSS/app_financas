<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearlyItemRecurrents extends BaseModel
{
    protected $fillable = [
        'recurrent_id',
        'payment_day',
        'reference_year',
        'amount',
        'status'
    ];
}
