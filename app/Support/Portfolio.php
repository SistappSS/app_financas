<?php

// app/Support/Portfolio.php
namespace App\Support;

use Illuminate\Support\Collection;

class Portfolio
{
    /**
     * @param Collection $trades -> cada item: ['side','quantity','price','fees']
     * @return array [qty, avg_price, invested, realized_pl]
     */
    public static function computePosition(Collection $trades): array
    {
        $qty = 0.0;
        $avg = 0.0;
        $invested = 0.0;
        $realized = 0.0;

        foreach ($trades as $t) {
            $q = (float)$t->quantity;
            $p = (float)$t->price;
            $f = (float)($t->fees ?? 0);

            if ($t->side === 'buy') {
                // novo PM: (PM*Q + p*q + fees) / (Q+q)
                $totalCost = $avg * $qty + ($p * $q) + $f;
                $qty += $q;
                $avg = $qty > 0 ? $totalCost / $qty : 0.0;
                $invested += ($p * $q) + $f;
            } else { // sell
                // P&L realizado: (p - PM)*q - fees
                $realized += (($p - $avg) * $q) - $f;
                $qty -= $q;
                if ($qty <= 0) {
                    $qty = 0.0; $avg = 0.0; // zera posição se vendeu tudo
                }
            }
        }

        return [
            'qty'         => $qty,
            'avg_price'   => $avg,
            'invested'    => $invested,
            'realized_pl' => $realized,
        ];
    }
}
