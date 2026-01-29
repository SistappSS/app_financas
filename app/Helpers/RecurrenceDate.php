<?php

namespace App\Helpers;

use Carbon\Carbon;

class RecurrenceDate
{
    public static function normalizeWeekends(Carbon $date, bool $includeSat, bool $includeSun): Carbon
    {
        $d = $date->copy();

        if (!$includeSat && $d->isSaturday()) $d->addDays(2);
        if (!$includeSun && $d->isSunday())   $d->addDay();

        return $d;
    }

    public static function next(Carbon $from, string $unit, int $value, bool $includeSat, bool $includeSun): Carbon
    {
        $next = match ($unit) {
            'days'   => $from->copy()->addDays($value),
            'months' => $from->copy()->addMonthsNoOverflow($value)->endOfDay(),
            'years'  => $from->copy()->addYearsNoOverflow($value)->endOfDay(),
            default  => $from->copy()->addMonth(),
        };

        return self::normalizeWeekends($next->startOfDay(), $includeSat, $includeSun);
    }
}
