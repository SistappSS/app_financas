<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CdiRate;
use Carbon\Carbon;

class UpdateCdiRates extends Command
{
    protected $signature = 'cdi:update';
    protected $description = 'Atualiza a taxa CDI DI diária usando a API do Banco Central';

    public function handle()
    {
        $this->info("🔄 Atualizando CDI (DI)…");

        try {
            // SERIES 11 = CDI DI
            $series = 11;

            // Sempre buscar dos últimos 7 dias para garantir pegar o último dia útil
            $dataFinal   = Carbon::now();
            $dataInicial = Carbon::now()->subDays(7);

            $url = "https://api.bcb.gov.br/dados/serie/bcdata.sgs.{$series}/dados";

            $res = Http::withHeaders(['Accept' => '*/*'])
                ->timeout(10)
                ->retry(3, 300)
                ->get($url, [
                    'formato'     => 'json',
                    'dataInicial' => $dataInicial->format('d/m/Y'),
                    'dataFinal'   => $dataFinal->format('d/m/Y')
                ]);

            $rows = $res->json();

            if (!is_array($rows) || empty($rows)) {
                $this->error("❌ API do BCB retornou vazio.");
                return 1;
            }

            // pega o último dia com valor disponível (último dia útil)
            $last = end($rows);

            if (!isset($last['valor'], $last['data'])) {
                $this->error("❌ Formato inesperado da API.");
                return 1;
            }

            $dataApi   = Carbon::createFromFormat('d/m/Y', $last['data']);
            $annual    = floatval(str_replace(',', '.', $last['valor'])) / 100;

            CdiRate::updateOrCreate(
                ['date' => $dataApi->toDateString()],
                ['annual_rate' => $annual]
            );

            $this->info("✅ CDI atualizado!");
            $this->info("📅 Data: " . $dataApi->toDateString());
            $this->info("📈 Taxa anual: {$annual}");

            return 0;
        }

        catch (\Exception $e) {
            $this->error("❗ Erro inesperado: " . $e->getMessage());
            return 1;
        }
    }
}
