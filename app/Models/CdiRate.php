<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CdiRate extends Model
{
    protected $table = 'cdi_rates';

    protected $primaryKey = 'date';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'date',
        'annual_rate',
    ];

    protected $casts = [
        'date'        => 'date',
        'annual_rate' => 'float',
    ];
}

