<?php

namespace App\Console\Commands;

use App\Services\InvestmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessSavingsAnniversaries extends Command
{
    /**
     * Nome do comando Artisan.
     *
     * Este é o nome que você vai usar no Kernel e no terminal:
     *
     * php artisan savings:process-anniversaries
     */
    protected $signature = 'savings:process-anniversaries';

    /**
     * Descrição do comando (exibido no `php artisan list`).
     */
    protected $description = 'Processa os aniversários mensais dos investimentos, aplicando rendimentos das cotas e rendimentos proporcionais pendentes.';

    /**
     * Handle → lógica principal.
     *
     * Aqui chamamos o InvestmentService para rodar o cálculo completo.
     */
    public function handle(InvestmentService $service)
    {
        $today = Carbon::now()->startOfDay();

        $this->info('-------------------------------------------');
        $this->info('PROCESSAMENTO DE ANIVERSÁRIOS DE INVESTIMENTOS');
        $this->info('Data: ' . $today->toDateString());
        $this->info('-------------------------------------------');
        $this->info('');

        try {
            $service->processAnniversaries($today);

            $this->info('✔ Processamento concluído com sucesso!');
        } catch (\Throwable $e) {
            $this->error('✘ ERRO ao processar aniversários:');
            $this->error($e->getMessage());
            $this->error('');

            // Mostra o trace no terminal se quiser debugar
            // $this->error($e->getTraceAsString());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
