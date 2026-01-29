<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Investment extends Model
{
    use HasUuids;

    // Laravel 10+ (gera UUID automaticamente)

    protected $fillable = [
        'user_id',
        'name',
        'purchase_value',
        'interest_rate',
        'rate_period',
        'start_date',
        'notes',
    ];

    protected $casts = [
        'purchase_value' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'start_date' => 'date',
    ];

// Atributos “virtuais” que já retornam no JSON
    protected $appends = [
        'monthly_yield_value',   // R$ ganho num mês (simples, não composto)
        'value_after_12_months', // R$ projetado em 12m (composto)
        'effective_monthly_rate' // taxa mensal efetiva (se vier a.a, converte)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

// Converte a taxa informada para taxa efetiva mensal (em decimal, ex.: 0.0110)
    public function getEffectiveMonthlyRateAttribute(): float
    {
        $ratePercent = (float)$this->interest_rate; // ex.: 1.10 (%)
        $rate = $ratePercent / 100.0;

        if ($this->rate_period === 'yearly') {
// converte taxa efetiva anual para mensal equivalente: (1+i_a)^(1/12)-1
            return pow(1 + $rate, 1 / 12) - 1;
        }

// já é mensal
        return $rate;
    }

// Rendimento de 1 mês em R$ (simples sobre o principal)
    public function getMonthlyYieldValueAttribute(): string
    {
        $yield = (float)$this->purchase_value * $this->effective_monthly_rate;
        return number_format($yield, 2, '.', '');
    }

// Projeção em 12 meses com juros compostos
    public function getValueAfter12MonthsAttribute(): string
    {
        $i = $this->effective_monthly_rate;
        $fv = (float)$this->purchase_value * pow(1 + $i, 12);
        return number_format($fv, 2, '.', '');
    }
}

