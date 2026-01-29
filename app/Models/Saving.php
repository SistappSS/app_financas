<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Saving extends Model
{
    use HasUuids;

    protected $table = 'savings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'account_id',
        'name',
        'current_amount',
        'interest_rate',
        'rate_period',
        'start_date',
        'notes',
        'cdi_percent',
        'color_card',
    ];

    protected $casts = [
        'current_amount' => 'float',
        'interest_rate'  => 'float',
        'start_date'     => 'date',
        'cdi_percent'    => 'float',
    ];


    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lots()
    {
        return $this->hasMany(SavingLot::class, 'saving_id');
    }


    // 🔥 RELACIONAMENTO IMPORTANTE
    public function movements()
    {
        return $this->hasMany(SavingMovement::class, 'saving_id');
    }

    // 🔥 Para somar depósitos / saques facilmente
    public function getBalanceAttribute()
    {
        $deposit = $this->movements()->where('direction', 'deposit')->sum('amount');
        $withdraw = $this->movements()->where('direction', 'withdraw')->sum('amount');

        return $deposit - $withdraw;
    }
    public function getTotalInvestedAttribute()
    {
        return $this->lots->sum('original_amount');
    }

    public function getActiveInvestedAttribute()
    {
        return $this->lots->sum('invested_amount');
    }

    public function getTotalYieldAttribute()
    {
        return $this->movements()
            ->where('direction', 'earning')
            ->sum('amount');
    }

    public function getNextYieldDateAttribute()
    {
        return $this->lots
            ->whereNull('closed_at')
            ->sortBy('next_yield_date')
            ->first()?->next_yield_date;
    }


}
