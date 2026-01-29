<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SavingLot extends Model
{
    use HasUuids;

    protected $table = 'saving_lots';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'saving_id',
        'original_amount',
        'invested_amount',
        'created_at',
        'last_principal_event',
        'next_yield_date',
        'closed_at',
    ];

    protected $casts = [
        'original_amount'      => 'float',
        'invested_amount'      => 'float',
        'created_at'           => 'date',
        'last_principal_event' => 'date',
        'next_yield_date'      => 'date',
        'closed_at'            => 'date',
    ];

    // 🔥 RELAÇÃO OBRIGATÓRIA (você removeu sem querer)
    public function parentSaving()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }


    public function movements()
    {
        return $this->hasMany(SavingMovement::class, 'lot_id');
    }

    public function pendingYields()
    {
        return $this->hasMany(SavingLotPendingYield::class, 'lot_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('closed_at')
            ->where('invested_amount', '>', 0);
    }


}
