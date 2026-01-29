<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CdiService
{
    private const SGS_SERIES_CDI = 12; // CDI - taxa diária oficial

    /**
     * Retorna o CDI DI diário ajustado automaticamente.
     */
    public function dailyRate(Carbon $date): float
    {
        return Cache::remember("cdi_dia_{$date->format('Y-m-d')}", now()->addHours(12), function () use ($date) {

            // 1) Tenta buscar o CDI do dia
            $rate = $this->fetchCdiForDate($date);
            if ($rate !== null) {
                return $rate;
            }

            // 2) Caso o dia seja fim de semana/feriado, tenta dia útil anterior
            $previous = $this->previousBusinessDay($date);
            $ratePrev = $this->fetchCdiForDate($previous);

            if ($ratePrev !== null) {
                return $ratePrev;
            }

            // 3) Fallback seguro (últimos 10 anos a média gira em torno disso)
            return 0.000452;
        });
    }

    /**
     * Busca o CDI de uma data específica.
     * Retorna null se o Banco Central não tiver valores.
     */
    private function fetchCdiForDate(Carbon $date): ?float
    {
        $dateStr = $date->format('d/m/Y');

        $url = "https://api.bcb.gov.br/dados/serie/bcdata.sgs." . self::SGS_SERIES_CDI . "/dados";

        $response = Http::get($url, [
            'formato'     => 'json',
            'dataInicial' => $dateStr,
            'dataFinal'   => $dateStr
        ]);

        if (!$response->ok()) {
            return null;
        }

        $json = $response->json();

        if (!isset($json[0]['valor'])) {
            return null;
        }

        $annualCdi = floatval(str_replace(',', '.', $json[0]['valor'])) / 100;

        // Converte CDI anual para diário (252 dias úteis)
        return pow(1 + $annualCdi, 1/252) - 1;
    }

    /**
     * Calcula o dia útil anterior.
     */
    private function previousBusinessDay(Carbon $date): Carbon
    {
        $d = $date->copy()->subDay();

        while ($d->isWeekend()) {
            $d->subDay();
        }

        return $d;
    }
}
