<?php

namespace App\Helpers;

use Carbon\Carbon;

class CardCycle
{
    public static function lastClose(Carbon $date, int $closingDay): Carbon
    {
        $d = $date->copy();
        if ($d->day >= $closingDay) return Carbon::create($d->year, $d->month, $closingDay);
        $prev = $d->copy()->subMonth();
        return Carbon::create($prev->year, $prev->month, $closingDay);
    }

    public static function cycleMonthFor(Carbon $date, int $closingDay): string
    {
        return $date->day > $closingDay
            ? $date->copy()->addMonth()->format('Y-m')
            : $date->copy()->format('Y-m');
    }
}
