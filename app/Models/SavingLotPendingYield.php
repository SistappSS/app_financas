<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingLotPendingYield extends Model
{
    use HasUuids;

    protected $table = 'saving_lot_pending_yields';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'lot_id',
        'saving_id',
        'base_amount',
        'days_invested',
        'yield_amount',
        'credit_date',
        'credited_at',
    ];

    protected $casts = [
        'base_amount'   => 'float',
        'days_invested' => 'int',
        'yield_amount'  => 'float',
        'credit_date'   => 'date',
        'credited_at'   => 'datetime',
    ];

    public function lot()
    {
        return $this->belongsTo(SavingLot::class, 'lot_id');
    }

    public function parentSaving()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }

}





