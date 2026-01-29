<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingMovement extends Model
{
    use HasUuids;

    protected $table = 'saving_movements';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'saving_id',
        'lot_id',
        'transaction_id',
        'account_id',
        'direction',
        'amount',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'date'   => 'datetime',
    ];

    public function lot()
    {
        return $this->belongsTo(SavingLot::class, 'lot_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function parentSaving()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }


}
